<?php declare(strict_types=1);

namespace Rami\B2BCartRules\Service;

/**
 * Validates product quantity rules independently of the Storefront or checkout.
 *
 * The caller supplies the product custom fields and the effective line-item
 * quantity. Integration with the cart and checkout is handled separately.
 */
final class ProductOrderRuleValidator
{
    public const MINIMUM_QUANTITY_FIELD = 'rami_b2b_minimum_order_quantity';

    public const QUANTITY_STEP_FIELD = 'rami_b2b_order_quantity_step';

    /**
     * @param array<string, mixed> $customFields Product custom fields
     *
     * @return list<array{code: string, message: string}>
     */
    public function validateQuantity(int $quantity, array $customFields): array
    {
        if ($quantity < 1) {
            return [[
                'code' => 'invalid_quantity',
                'message' => 'The product quantity must be at least 1.',
            ]];
        }

        $violations = [];

        $minimumQuantity = $this->readPositiveInteger(
            $customFields,
            self::MINIMUM_QUANTITY_FIELD,
            $violations,
        );

        $quantityStep = $this->readPositiveInteger(
            $customFields,
            self::QUANTITY_STEP_FIELD,
            $violations,
        );

        if ($violations !== []) {
            // Invalid rule configuration must not silently disable restrictions.
            return $violations;
        }

        if ($minimumQuantity !== null && $quantity < $minimumQuantity) {
            $violations[] = [
                'code' => 'minimum_quantity',
                'message' => sprintf(
                    'The minimum order quantity for this product is %d.',
                    $minimumQuantity,
                ),
            ];
        }

        if ($quantityStep !== null && $quantity % $quantityStep !== 0) {
            $violations[] = [
                'code' => 'quantity_step',
                'message' => sprintf(
                    'The order quantity must be a multiple of %d.',
                    $quantityStep,
                ),
            ];
        }

        return $violations;
    }

    /**
     * Empty fields mean that no restriction is configured.
     *
     * @param array<string, mixed> $customFields
     * @param list<array{code: string, message: string}> $violations
     */
    private function readPositiveInteger(
        array $customFields,
        string $field,
        array &$violations,
    ): ?int {
        $value = $customFields[$field] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if ((!is_int($value) && !(is_string($value) && ctype_digit($value))) || (int) $value < 1) {
            $violations[] = [
                'code' => 'invalid_rule_configuration',
                'message' => sprintf('The configured value for "%s" must be a positive integer.', $field),
            ];

            return null;
        }

        return (int) $value;
    }
}
