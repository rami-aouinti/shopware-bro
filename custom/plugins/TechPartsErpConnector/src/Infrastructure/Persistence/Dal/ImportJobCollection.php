<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Persistence\Dal;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/** @extends EntityCollection<ImportJobEntity> */
class ImportJobCollection extends EntityCollection
{
    protected function getExpectedClass(): string { return ImportJobEntity::class; }
}
