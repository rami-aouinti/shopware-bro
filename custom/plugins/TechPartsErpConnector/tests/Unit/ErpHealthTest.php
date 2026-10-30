<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechPartsErpConnector\Domain\Model\ErpHealth;

final class ErpHealthTest extends TestCase
{
    public function testHealth(): void
    {
        self::assertFalse((new ErpHealth(false, 'unconfigured'))->available);
    }

    public function testProviderCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ErpHealth(true, ' ');
    }
}
