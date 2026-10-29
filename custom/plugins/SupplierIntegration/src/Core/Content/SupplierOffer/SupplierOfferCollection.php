<?php declare(strict_types=1);
namespace SupplierIntegration\\Core\\Content\\SupplierOffer;
use Shopware\\Core\\Framework\\DataAbstractionLayer\\EntityCollection;
/** @extends EntityCollection<SupplierOfferEntity> */
class SupplierOfferCollection extends EntityCollection
{
    protected function getExpectedClass(): string { return SupplierOfferEntity::class; }
}
