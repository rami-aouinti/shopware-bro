<?php declare(strict_types=1);

namespace SupplierIntegration\\Core\\Content\\Supplier;

use Shopware\\Core\\Framework\\DataAbstractionLayer\\EntityDefinition;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\BoolField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\CreatedAtField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\FkField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\IdField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\LongTextField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\StringField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\UpdatedAtField;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\Flag\\PrimaryKey;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\Flag\\Required;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\FieldCollection;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\Field\\OneToManyAssociationField;

class SupplierDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'supplier_integration_supplier';
    public function getEntityName(): string { return self::ENTITY_NAME; }
    public function getEntityClass(): string { return SupplierEntity::class; }
    public function getCollectionClass(): string { return SupplierCollection::class; }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new StringField('code', 'code'))->addFlags(new Required()),
            new StringField('api_url', 'apiUrl'),
            new StringField('api_key_env', 'apiKeyEnv'),
            new BoolField('active', 'active'),
            new BoolField('demo_mode', 'demoMode'),
            new LongTextField('configuration', 'configuration'),
            new CreatedAtField(),
            new UpdatedAtField(),
            new OneToManyAssociationField('offers', SupplierOfferDefinition::class, 'supplier_id'),
            new OneToManyAssociationField('syncRuns', SupplierSyncRunDefinition::class, 'supplier_id'),
        ]);
    }
}
