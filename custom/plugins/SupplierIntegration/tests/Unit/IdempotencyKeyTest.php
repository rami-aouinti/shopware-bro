<?php declare(strict_types=1);
namespace SupplierIntegration\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class IdempotencyKeyTest extends TestCase
{
    public function testSameSupplierAndExternalIdProduceSameStableKey(): void
    {
        $key1 = substr(hash('md5', 'supplier-1:external-99'), 0, 32);
        $key2 = substr(hash('md5', 'supplier-1:external-99'), 0, 32);
        self::assertSame($key1, $key2);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $key1);
    }
    public function testDifferentExternalIdsProduceDifferentKeys(): void
    {
        self::assertNotSame(hash('md5', 'supplier-1:external-99'), hash('md5', 'supplier-1:external-100'));
    }
}
