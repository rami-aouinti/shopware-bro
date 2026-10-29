<?php declare(strict_types=1);

namespace SupplierIntegration\Tests;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Migration\MigrationStep;
use SupplierIntegration\Migration\Migration1761700000CreateSupplierIntegrationTables;

final class MigrationDiscoveryTest extends TestCase
{
    public function testMigrationClassCanBeLoadedAndExtendsMigrationStep(): void
    {
        self::assertTrue(
            class_exists(Migration1761700000CreateSupplierIntegrationTables::class),
            'Composer must be able to autoload the supplier integration migration.'
        );

        self::assertTrue(
            is_subclass_of(Migration1761700000CreateSupplierIntegrationTables::class, MigrationStep::class),
            'Shopware migration discovery only accepts classes extending MigrationStep.'
        );
    }

    public function testMigrationFileAndNamespaceFollowPluginConvention(): void
    {
        $migrationFile = dirname(__DIR__) . '/src/Migration/Migration1761700000CreateSupplierIntegrationTables.php';

        self::assertFileExists($migrationFile);
        self::assertSame(
            'SupplierIntegration\\Migration',
            (new \ReflectionClass(Migration1761700000CreateSupplierIntegrationTables::class))->getNamespaceName()
        );
    }
}
