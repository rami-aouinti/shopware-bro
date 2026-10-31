<?php declare(strict_types=1);
namespace EnterpriseOrderIntegration\Tests\Unit;
use EnterpriseOrderIntegration\Application\Message\ExportOrderMessage;
use PHPUnit\Framework\TestCase;
final class ExportOrderMessageTest extends TestCase
{
    public function testMessageKeepsOrderAndCorrelationIds(): void
    {
        $message = new ExportOrderMessage('0123456789abcdef0123456789abcdef', 'corr-123');
        self::assertSame('0123456789abcdef0123456789abcdef', $message->orderId);
        self::assertSame('corr-123', $message->correlationId);
    }
}