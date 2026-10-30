<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Domain\Contract;

use TechPartsErpConnector\Domain\Model\ErpHealth;

interface ErpGateway
{
    public function health(): ErpHealth;
}
