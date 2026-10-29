<?php declare(strict_types=1);
namespace SupplierIntegration\\Core\\Content\\SupplierSyncRun;
use SupplierIntegration\\Core\\Content\\Supplier\\SupplierDefinition;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\EntityDefinition;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\FkField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\IdField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\IntField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\StringField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\DateTimeField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\CreatedAtField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\ManyToOneAssociationField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\OneToManyAssociationField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\Flag\\PrimaryKey;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\Flag\\Required;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\FieldCollection;
class SupplierSyncRunDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'supplier_integration_sync_run';
    public function getEntityName(): string { return self::ENTITY_NAME; }
    public function getEntityClass(): string { return SupplierSyncRunEntity::class; }
    public function getCollectionClass(): string { return SupplierSyncRunCollection::class; }
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('supplier_id', 'supplierId', SupplierDefinition::class))->addFlags(new Required()),
            (new StringField('status', 'status'))->addFlags(new Required()),
            new StringField('correlation_id', 'correlationId'),
            new DateTimeField('started_at', 'startedAt'),
            new DateTimeField('finished_at', 'finishedAt'),
            new IntField('items_received', 'itemsReceived'),
            new IntField('items_created', 'itemsCreated'),
            new IntField('items_updated', 'itemsUpdated'),
            new IntField('items_failed', 'itemsFailed'),
            new StringField('last_error', 'lastError'),
            new CreatedAtField(),
            new ManyToOneAssociationField('supplier', 'supplier_id', SupplierDefinition::class, 'id', false),
            new OneToManyAssociationField('errors', SupplierSyncErrorDefinition::class, 'sync_run_id'),
        ]);
    }
}
