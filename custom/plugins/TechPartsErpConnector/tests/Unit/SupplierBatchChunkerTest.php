<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechPartsErpConnector\Application\Import\SupplierBatchChunker;
use TechPartsErpConnector\Application\Import\SupplierImportRow;

final class SupplierBatchChunkerTest extends TestCase
{
    public function testCreatesBoundedBatches(): void
    {
        $rows = [
            new SupplierImportRow('BOSCH', '1', null),
            new SupplierImportRow('BOSCH', '2', null),
            new SupplierImportRow('BOSCH', '3', null),
            new SupplierImportRow('BOSCH', '4', null),
            new SupplierImportRow('BOSCH', '5', null),
        ];

        $batches = iterator_to_array((new SupplierBatchChunker())->chunk($rows, 2), false);
        self::assertCount(3, $batches);
        self::assertSame([2, 2, 1], array_map('count', $batches));
        self::assertSame('5', $batches[2][0]['externalProductId']);
    }

    public function testRejectsInvalidBatchSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        iterator_to_array((new SupplierBatchChunker())->chunk([], 0));
    }
}
