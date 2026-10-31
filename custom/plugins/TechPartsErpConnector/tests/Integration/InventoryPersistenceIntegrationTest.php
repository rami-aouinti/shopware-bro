<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Integration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use PHPUnit\Framework\TestCase;

/**
 * Requires the disposable Shopware DB and the ERP plugin migrations.
 * Runs in a rollback-only transaction; never use this suite against production.
 */
final class InventoryPersistenceIntegrationTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $url = $_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
        if (!is_string($url) || $url === '') {
            self::markTestSkipped('DATABASE_URL must point to a dedicated test database.');
        }

        // Shopware's DATABASE_URL normally uses mysql://. DBAL 4 requires an
        // explicit mapping from that scheme to its concrete driver name.
        $connectionParams = (new DsnParser(['mysql' => 'pdo_mysql']))->parse($url);
        $this->connection = DriverManager::getConnection($connectionParams);
        if (!$this->connection->createSchemaManager()->tablesExist(['tp_inventory_sync'])) {
            self::markTestSkipped('Run Shopware ERP migrations first.');
        }

        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->connection) && $this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }

    public function testSameSourceAndEventCanOnlyBeInsertedOnce(): void
    {
        $source = 'TEST_ERP';
        $eventId = 'integration-' . bin2hex(random_bytes(10));
        $productId = random_bytes(16);

        self::assertSame(1, $this->insertInventoryEvent($source, $eventId, $productId));
        self::assertSame(0, $this->insertInventoryEvent($source, $eventId, $productId));

        self::assertSame(1, (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tp_inventory_sync WHERE source = :source AND external_event_id = :eventId',
            ['source' => $source, 'eventId' => $eventId],
        ));
    }

    public function testSameEventIdIsAllowedForDifferentSources(): void
    {
        $eventId = 'integration-' . bin2hex(random_bytes(10));
        $productId = random_bytes(16);

        self::assertSame(1, $this->insertInventoryEvent('TEST_ERP_A', $eventId, $productId));
        self::assertSame(1, $this->insertInventoryEvent('TEST_ERP_B', $eventId, $productId));
    }

    private function insertInventoryEvent(string $source, string $eventId, string $productId): int
    {
        return $this->connection->executeStatement(
            <<<'SQL'
INSERT IGNORE INTO tp_inventory_sync (
    id, source, external_event_id, product_id, external_quantity,
    safety_stock, applied_stock, event_version, status, created_at, updated_at
) VALUES (
    :id, :source, :eventId, :productId, 25, 5, 20, 100, 'applied', NOW(3), NULL
)
SQL,
            [
                'id' => random_bytes(16),
                'source' => $source,
                'eventId' => $eventId,
                'productId' => $productId,
            ],
        );
    }
}
