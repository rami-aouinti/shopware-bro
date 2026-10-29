<?php declare(strict_types=1);

namespace SupplierIntegration;

use Shopware\Core\Framework\Plugin;

final class SupplierIntegration extends Plugin
{
    /**
     * Return the migration namespace, not the fully qualified plugin class name.
     *
     * Using self::class here produces SupplierIntegration\SupplierIntegration\Migration,
     * which does not match the classes stored in src/Migration.
     */
    public function getMigrationNamespace(): string
    {
        return __NAMESPACE__ . '\\Migration';
    }
}
