<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Infrastructure\Erp;

use TechPartsErpConnector\Domain\Contract\ErpGateway;
use TechPartsErpConnector\Domain\Model\ErpHealth;

final class UnavailableErpGateway implements ErpGateway
{
    #[\Override]
    public function health(): ErpHealth
    {
        return new ErpHealth(false, 'unconfigured');
    }
}
