<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechPartsErpConnector\Domain\Model\SupplierReference;
use TechPartsErpConnector\Domain\Service\SupplierReferenceId;

final class SupplierReferenceIdTest extends TestCase
{
    public function testSameSupplierReferenceAlwaysProducesSameId(): void
    {
        $first = new SupplierReference('BOSCH', '4711');
        $second = new SupplierReference('BOSCH', '4711');

        self::assertSame(
            SupplierReferenceId::fromReference($first),
            SupplierReferenceId::fromReference($second),
        );
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', SupplierReferenceId::fromReference($first));
    }

    public function testDifferentSupplierReferencesGetDifferentIds(): void
    {
        $first = new SupplierReference('BOSCH', '4711');
        $second = new SupplierReference('VALEO', '4711');

        self::assertNotSame(
            SupplierReferenceId::fromReference($first),
            SupplierReferenceId::fromReference($second),
        );
    }
}
