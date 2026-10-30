<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechPartsErpConnector\Infrastructure\Erp\ErpClientConfig;

final class ErpClientConfigTest extends TestCase
{
    public function testRejectsInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ErpClientConfig('file:///tmp', 'token');
    }

    public function testRejectsUnsafeTimeout(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ErpClientConfig('https://erp.example.test', 'token', 0);
    }
}
