<?php declare(strict_types=1);

namespace EnterpriseOrderIntegration;

use Shopware\Core\Framework\Plugin;

class EnterpriseOrderIntegration extends Plugin
{
    public function getMigrationNamespace(): string
    {
        return __NAMESPACE__ . '\\Migration';
    }
}
