<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Persistence\Dal;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/** @extends EntityCollection<SupplierCatalogEntity> */
class SupplierCatalogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SupplierCatalogEntity::class;
    }
}
