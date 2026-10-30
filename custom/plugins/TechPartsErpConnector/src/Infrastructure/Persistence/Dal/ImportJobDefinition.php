<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Persistence\Dal;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ImportJobDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'tp_import_job';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ImportJobEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ImportJobCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('source', 'source'))->addFlags(new Required()),
            (new StringField('status', 'status'))->addFlags(new Required()),
            (new IntField('received', 'received'))->addFlags(new Required()),
            (new IntField('accepted', 'accepted'))->addFlags(new Required()),
            (new IntField('rejected', 'rejected'))->addFlags(new Required()),
            new LongTextField('error_summary', 'errorSummary'),
            new DateTimeField('started_at', 'startedAt'),
            new DateTimeField('finished_at', 'finishedAt'),
        ]);
    }
}
