<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Domain\Service;

use TechPartsErpConnector\Domain\Model\SupplierReference;

/**
 * Single deterministic identity for an external supplier reference.
 *
 * Important: existing rows generated with a different algorithm must be
 * reconciled before deploying this change against a populated catalog.
 */
final class SupplierReferenceId
{
    public static function fromReference(SupplierReference $reference): string
    {
        return substr(
            hash('sha256', "techparts:supplier-reference:v1\0" . $reference->key()),
            0,
            32,
        );
    }
}
