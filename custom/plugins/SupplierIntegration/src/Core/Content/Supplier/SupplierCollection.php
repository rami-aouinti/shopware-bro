<?php declare(strict_types=1);

namespace SupplierIntegration\Core\Content\Supplier;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<SupplierEntity>
 */
class SupplierCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SupplierEntity::class;
    }
}
