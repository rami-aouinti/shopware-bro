<?php declare(strict_types=1);
namespace SupplierIntegration\Message;
final class IndexSupplierOfferMessage
{
    /** @param list<string> $offerIds */
    public function __construct(public readonly array $offerIds) {}
}
