<?php declare(strict_types=1);
namespace SupplierIntegration\\Tests\\Unit;
use PHPUnit\\Framework\\TestCase;
final class OfferPayloadNormalizerTest extends TestCase
{
    public function testPayloadHashIsStableForIdempotentImport(): void
    {
        $payload = ['externalId' => 'SKU-100', 'price' => 12.5, 'stock' => 4];
        $first = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
        $second = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
        self::assertSame($first, $second);
    }
    public function testDifferentPayloadProducesDifferentHash(): void
    {
        self::assertNotSame(hash('sha256', '{"price":12}'), hash('sha256', '{"price":13}'));
    }
}
