<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Handler;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use TechPartsErpConnector\Application\Command\RegisterSupplierReference;
use TechPartsErpConnector\Domain\Model\SupplierReference;

final readonly class RegisterSupplierReferenceHandler
{
    public function __construct(private EntityRepository $supplierCatalogRepository)
    {
    }

    public function handle(RegisterSupplierReference $command, Context $context): void
    {
        $reference = new SupplierReference($command->supplierCode, $command->externalProductId);
        if ($command->productId !== null && !Uuid::isValid($command->productId)) {
            throw new \InvalidArgumentException('Invalid Shopware product UUID');
        }
        $this->supplierCatalogRepository->upsert([[
            'id' => Uuid::fromStringToHex($reference->key()),
            'supplierCode' => $reference->supplierCode,
            'externalProductId' => $reference->externalProductId,
            'productId' => $command->productId,
        ]], $context);
    }
}
