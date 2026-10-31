<?php declare(strict_types=1);

namespace Rami\B2BCartRules\Tests\Service;

use PHPUnit\Framework\TestCase;
use Rami\B2BCartRules\Service\ProductOrderRuleValidator;

final class ProductOrderRuleValidatorTest extends TestCase
{
    private ProductOrderRuleValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ProductOrderRuleValidator();
    }

    public function testQuantityIsValidWhenNoRulesAreConfigured(): void
    {
        self::assertSame([], $this->validator->validateQuantity(3, []));
    }

    public function testEmptyOptionalFieldsDoNotRestrictQuantity(): void
    {
        self::assertSame([], $this->validator->validateQuantity(3, [
            ProductOrderRuleValidator::MINIMUM_QUANTITY_FIELD => '',
            ProductOrderRuleValidator::QUANTITY_STEP_FIELD => null,
        ]));
    }

    public function testQuantityBelowMinimumIsRejected(): void
    {
        $violations = $this->validator->validateQuantity(5, [
            ProductOrderRuleValidator::MINIMUM_QUANTITY_FIELD => 6,
        ]);

        self::assertSame('minimum_quantity', $violations[0]['code']);
    }

    public function testQuantityThatIsNotAMultipleIsRejected(): void
    {
        $violations = $this->validator->validateQuantity(8, [
            ProductOrderRuleValidator::QUANTITY_STEP_FIELD => 6,
        ]);

        self::assertSame('quantity_step', $violations[0]['code']);
    }

    public function testQuantityCanPassBothMinimumAndStepRules(): void
    {
        $violations = $this->validator->validateQuantity(12, [
            ProductOrderRuleValidator::MINIMUM_QUANTITY_FIELD => 6,
            ProductOrderRuleValidator::QUANTITY_STEP_FIELD => 6,
        ]);

        self::assertSame([], $violations);
    }

    public function testQuantityCanReturnMinimumAndStepViolationsTogether(): void
    {
        $violations = $this->validator->validateQuantity(5, [
            ProductOrderRuleValidator::MINIMUM_QUANTITY_FIELD => 6,
            ProductOrderRuleValidator::QUANTITY_STEP_FIELD => 4,
        ]);

        self::assertSame(['minimum_quantity', 'quantity_step'], array_column($violations, 'code'));
    }

    public function testPositiveIntegerStringsAreAcceptedFromCustomFields(): void
    {
        self::assertSame([], $this->validator->validateQuantity(12, [
            ProductOrderRuleValidator::MINIMUM_QUANTITY_FIELD => '6',
            ProductOrderRuleValidator::QUANTITY_STEP_FIELD => '6',
        ]));
    }

    public function testInvalidRuleConfigurationFailsClosed(): void
    {
        $violations = $this->validator->validateQuantity(12, [
            ProductOrderRuleValidator::QUANTITY_STEP_FIELD => '0',
        ]);

        self::assertSame('invalid_rule_configuration', $violations[0]['code']);
    }

    public function testInvalidMinimumConfigurationFailsClosed(): void
    {
        $violations = $this->validator->validateQuantity(12, [
            ProductOrderRuleValidator::MINIMUM_QUANTITY_FIELD => -1,
        ]);

        self::assertSame('invalid_rule_configuration', $violations[0]['code']);
    }

    public function testNonPositiveQuantityIsRejected(): void
    {
        $violations = $this->validator->validateQuantity(0, []);

        self::assertSame('invalid_quantity', $violations[0]['code']);
    }
}
