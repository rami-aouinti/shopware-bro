<?php declare(strict_types=1);

namespace SupplierIntegration\Core\Content\SupplierSyncError;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use SupplierIntegration\Core\Content\SupplierSyncRun\SupplierSyncRunDefinition;

class SupplierSyncErrorDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'supplier_integration_sync_error';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SupplierSyncErrorEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SupplierSyncErrorCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('sync_run_id', 'syncRunId', SupplierSyncRunDefinition::class))->addFlags(new Required()),
            new StringField('external_id', 'externalId'),
            (new StringField('error_code', 'errorCode'))->addFlags(new Required()),
            (new LongTextField('message', 'message'))->addFlags(new Required()),
            new LongTextField('context', 'context'),
            new CreatedAtField(),
            new ManyToOneAssociationField('syncRun', 'sync_run_id', SupplierSyncRunDefinition::class, 'id', false),
        ]);
    }
}
