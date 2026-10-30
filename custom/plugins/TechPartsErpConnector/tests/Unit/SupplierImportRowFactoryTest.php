<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechPartsErpConnector\Application\Import\SupplierImportRowFactory;

final class SupplierImportRowFactoryTest extends TestCase
{
    public function testValidRow(): void
    {
        $row = (new SupplierImportRowFactory())->fromArray(['supplierCode' => 'BOSCH', 'externalProductId' => '123']);
        self::assertSame('BOSCH', $row->supplierCode);
        self::assertNull($row->productId);
    }

    public function testRejectsMissingSupplierCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new SupplierImportRowFactory())->fromArray(['externalProductId' => '123']);
    }
}
