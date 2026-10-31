<?php declare(strict_types=1);

namespace Rami\B2BCartRules\Service;

/**
 * Pure validation for the configured minimum merchandise value.
 *
 * The cart integration supplies Shopware's calculated merchandise position
 * total, so shipping costs are not part of the threshold.
 */
final class MinimumOrderValueValidator
{
    /**
     * @return array{code: string, minimumValue: float, currentValue: float, remainingValue: float}|null
     */
    public function validate(float $currentValue, mixed $configuredMinimum, bool $enabled): ?array
    {
        if (!$enabled) {
            return null;
        }

        if (!is_numeric($configuredMinimum) || (float) $configuredMinimum < 0) {
            return [
                'code' => 'invalid_minimum_order_configuration',
                'minimumValue' => 0.0,
                'currentValue' => $currentValue,
                'remainingValue' => 0.0,
            ];
        }

        $minimumValue = round((float) $configuredMinimum, 2);
        $currentValue = round($currentValue, 2);

        if ($currentValue + 0.00001 >= $minimumValue) {
            return null;
        }

        return [
            'code' => 'minimum_order_value',
            'minimumValue' => $minimumValue,
            'currentValue' => $currentValue,
            'remainingValue' => round($minimumValue - $currentValue, 2),
        ];
    }
}
