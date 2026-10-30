<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechPartsErpConnector\Domain\Model\StockSnapshot;

final class StockSnapshotTest extends TestCase
{
    public function testSafetyStockIsSubtractedFromExternalQuantity(): void
    {
        $snapshot = new StockSnapshot('ACME_ERP', 'event-123', str_repeat('a', 32), 25, 5);
        self::assertSame(20, $snapshot->sellableQuantity());
    }

    public function testSellableQuantityNeverBecomesNegative(): void
    {
        $snapshot = new StockSnapshot('ACME_ERP', 'event-124', str_repeat('a', 32), 3, 8);
        self::assertSame(0, $snapshot->sellableQuantity());
    }

    public function testNegativeQuantityIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StockSnapshot('ACME_ERP', 'event-125', str_repeat('a', 32), -1, 0);
    }

    public function testInvalidProductIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StockSnapshot('ACME_ERP', 'event-126', 'not-a-uuid', 10, 0);
    }

    public function testBlankEventIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StockSnapshot('ACME_ERP', ' ', str_repeat('a', 32), 10, 0);
    }
    public function testAcceptsMonotonicStockVersion(): void
    {
        $snapshot = new StockSnapshot('ACME_ERP', 'event-200', str_repeat('a', 32), 25, 5, 42);
        self::assertSame(42, $snapshot->version);
        self::assertSame(20, $snapshot->sellableQuantity());
    }

    public function testNegativeStockVersionIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StockSnapshot('ACME_ERP', 'event-201', str_repeat('a', 32), 25, 5, -1);
    }

}
