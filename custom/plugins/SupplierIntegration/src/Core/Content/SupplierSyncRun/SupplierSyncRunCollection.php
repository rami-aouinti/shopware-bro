<?php declare(strict_types=1);
namespace SupplierIntegration\Core\Content\SupplierSyncRun;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
/** @extends EntityCollection<SupplierSyncRunEntity> */
class SupplierSyncRunCollection extends EntityCollection
{
    protected function getExpectedClass(): string { return SupplierSyncRunEntity::class; }
}
