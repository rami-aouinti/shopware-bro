<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechPartsErpConnector\Domain\Model\SupplierReference;

final class SupplierReferenceTest extends TestCase
{
    public function testKey(): void
    {
        self::assertSame('BOSCH:4711', (new SupplierReference('BOSCH', '4711'))->key());
    }

    public function testInvalidCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SupplierReference('?', '4711');
    }
}
