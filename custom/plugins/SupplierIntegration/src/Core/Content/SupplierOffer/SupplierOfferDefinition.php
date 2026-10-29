<?php declare(strict_types=1);
namespace SupplierIntegration\Core\Content\SupplierOffer;
use SupplierIntegration\Core\Content\Supplier\SupplierDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
class SupplierOfferDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'supplier_integration_offer';
    public function getEntityName(): string { return self::ENTITY_NAME; }
    public function getEntityClass(): string { return SupplierOfferEntity::class; }
    public function getCollectionClass(): string { return SupplierOfferCollection::class; }
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('supplier_id', 'supplierId', SupplierDefinition::class))->addFlags(new Required()),
            (new StringField('external_id', 'externalId'))->addFlags(new Required()),
            (new StringField('sku', 'sku'))->addFlags(new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            new StringField('category', 'category'),
            new FloatField('price', 'price'),
            new StringField('currency', 'currency'),
            new IntField('stock', 'stock'),
            new IntField('delivery_days', 'deliveryDays'),
            new StringField('payload_hash', 'payloadHash'),
            new BoolField('is_active', 'isActive'),
            new CreatedAtField(),
            new UpdatedAtField(),
            new ManyToOneAssociationField('supplier', 'supplier_id', SupplierDefinition::class, 'id', false),
        ]);
    }
}
