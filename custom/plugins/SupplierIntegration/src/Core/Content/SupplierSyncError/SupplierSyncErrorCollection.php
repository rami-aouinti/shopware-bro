<?php declare(strict_types=1);
namespace SupplierIntegration\Core\Content\SupplierSyncError;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
/** @extends EntityCollection<SupplierSyncErrorEntity> */
class SupplierSyncErrorCollection extends EntityCollection
{
    protected function getExpectedClass(): string { return SupplierSyncErrorEntity::class; }
}
