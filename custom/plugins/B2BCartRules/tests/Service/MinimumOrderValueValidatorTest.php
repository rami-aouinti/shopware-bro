<?php declare(strict_types=1);

namespace Rami\\B2BCartRules\\Tests\\Service;

use PHPUnit\\Framework\\TestCase;
use Rami\\B2BCartRules\\Service\\MinimumOrderValueValidator;

final class MinimumOrderValueValidatorTest extends TestCase
{
    private MinimumOrderValueValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new MinimumOrderValueValidator();
    }

    public function testValueBelowMinimumIsRejected(): void
    {
        $result = $this->validator->validate(199.99, '250.00', true);

        self::assertNotNull($result);
        self::assertSame('minimum_order_value', $result['code']);
        self::assertSame(50.01, $result['remainingValue']);
    }

    public function testExactMinimumIsAccepted(): void
    {
        self::assertNull($this->validator->validate(250.00, '250.00', true));
    }

    public function testValueAboveMinimumIsAccepted(): void
    {
        self::assertNull($this->validator->validate(300.00, '250.00', true));
    }

    public function testDisabledRuleDoesNotRejectCart(): void
    {
        self::assertNull($this->validator->validate(1.00, '250.00', false));
    }

    public function testZeroMinimumAllowsAnyNonNegativeCartValue(): void
    {
        self::assertNull($this->validator->validate(0.00, '0.00', true));
    }

    public function testInvalidConfigurationFailsClosed(): void
    {
        $result = $this->validator->validate(500.00, 'invalid', true);

        self::assertNotNull($result);
        self::assertSame('invalid_minimum_order_configuration', $result['code']);
    }

    public function testNegativeConfigurationFailsClosed(): void
    {
        $result = $this->validator->validate(500.00, '-1', true);

        self::assertNotNull($result);
        self::assertSame('invalid_minimum_order_configuration', $result['code']);
    }
}
