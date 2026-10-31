# B2B Cart Rules

A Shopware 6.7 plugin that validates B2B product quantities during cart calculation and prevents order submission when product quantity rules are violated.

## Scope by ticket

### SW-101 — Plugin skeleton
- Shopware plugin metadata and main plugin class.
- Plugin configuration and Symfony service discovery.
- Installation, update, and verification instructions.

### SW-102 — Product order rule fields
The declarative field set in `src/Resources/config/custom-fields.xml` adds these optional product custom fields:
- `rami_b2b_minimum_order_quantity`: positive integer minimum quantity.
- `rami_b2b_order_quantity_step`: positive integer multiple/step.

An empty field means that the corresponding restriction is not configured.

### SW-103 — Testable quantity rule service
`Rami\\B2BCartRules\\Service\\ProductOrderRuleValidator` validates the effective quantity and returns coded violations. It checks quantities below 1, configured minimums, configured multiples, and invalid rule configuration. Invalid configuration fails closed rather than silently removing the restriction.

### SW-104 — Cart validation integration
`ProductOrderRuleCartValidator` implements Shopware's `CartValidatorInterface` and is registered through `Resources/config/services.xml`. It:
- Reads product custom fields from the product repository.
- Aggregates quantities when the same product occurs in more than one line item.
- Adds stable cart errors for each rule violation.
- Respects `B2BCartRules.config.enabled`; an unset setting uses the default enabled behavior.

### SW-105 — Checkout enforcement
Rule violations are added as `GenericCartError` instances with `blockOrder = true`. Errors are non-persistent so Shopware can recalculate them after the cart changes. Storefront and Store API order flows must be tested locally to confirm the expected behavior in the installed Shopware version.

### SW-106 — Translations
English (`en-GB`) and German (`de-DE`) message catalogues are provided under `src/Resources/translations/`. The cart validator translates the error message before attaching it to the cart error.

### SW-107 — Automated tests
Unit tests for the rule service cover optional fields, minimum quantities, multiples, combined violations, numeric strings, invalid configuration, and non-positive quantities. Cart/checkout behavior still requires an integration or manual test against the running Shopware application.

### SW-108 — Documentation and release readiness
This README documents installation, configuration, test commands, and acceptance scenarios. Do not treat a release as production-verified until the local automated tests and Storefront/Store API acceptance checks below pass.

## Requirements

- Shopware 6.7.13 or newer for declarative `custom-fields.xml`.
- PHP version compatible with the host Shopware project (the plugin package requires PHP 8.3+).
- Plugin located under `custom/plugins/B2BCartRules`.
- Composer dependencies installed in the Shopware project.

## Install or update in Docker

Run from the Shopware project root. The Compose service in this project is named `web`.

For a fresh installation:

```bash
docker compose exec web php bin/console plugin:refresh
docker compose exec web php bin/console plugin:install --activate B2BCartRules
docker compose exec web php bin/console cache:clear
docker compose exec web php bin/console plugin:list
```

If the plugin is already installed:

```bash
docker compose exec web php bin/console plugin:update B2BCartRules
docker compose exec web php bin/console cache:clear
```

If it is installed but inactive:

```bash
docker compose exec web php bin/console plugin:activate B2BCartRules
docker compose exec web php bin/console cache:clear
```

## Automated and configuration checks

```bash
docker compose exec web php -l custom/plugins/B2BCartRules/src/B2BCartRules.php
docker compose exec web php -l custom/plugins/B2BCartRules/src/Service/ProductOrderRuleValidator.php
docker compose exec web php -l custom/plugins/B2BCartRules/src/Service/ProductOrderRuleCartValidator.php
docker compose exec web php -l custom/plugins/B2BCartRules/tests/Service/ProductOrderRuleValidatorTest.php
docker compose exec web vendor/bin/phpunit custom/plugins/B2BCartRules/tests/Service/ProductOrderRuleValidatorTest.php
docker compose exec web xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/config.xml
docker compose exec web xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/services.xml
docker compose exec web xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/custom-fields.xml
docker compose exec web php bin/console cache:clear
docker compose exec web php bin/console debug:container "Rami\\B2BCartRules\\Service\\ProductOrderRuleCartValidator"
```

These commands are the validation procedure; their inclusion here is not a claim that all tests have already been executed.

## Product configuration in Administration

1. Open **Catalogues > Products** (labels may vary by Shopware language).
2. Edit a product and find **Product order rules / Bestellregeln für Produkte**.
3. Set minimum quantity to `6` and quantity step to `6`.
4. Save, reopen the product, and confirm the values persist.

Expected quantity behavior:

| Quantity | Expected result |
|---:|---|
| 5 | Rejected: below minimum |
| 6 | Accepted |
| 7 | Rejected: not a multiple of 6 |
| 12 | Accepted |

## Checkout acceptance scenarios

Run these checks in a non-production environment with a valid sales channel and a test product:

1. Add quantity `5`; verify a readable cart error appears and order submission is blocked.
2. Attempt Store API order submission with the invalid cart; verify no order is created.
3. Change the quantity to `6` or `12`; verify the rule error disappears and a valid order can be submitted.
4. Use quantity `7`; verify the step violation blocks order submission.
5. Set `B2BCartRules.config.enabled` to `false`; verify these custom quantity rules no longer block the cart.
6. Repeat with both English and German storefront languages and verify the messages are translated.
7. Verify that correcting the cart removes the non-persistent error on recalculation.

## Global plugin configuration

- `B2BCartRules.config.enabled`: enable or disable the product quantity rules (default: `true`).
- `B2BCartRules.config.minimumOrderEnabled`: reserved configuration flag for minimum order value (default: `true`).
- `B2BCartRules.config.minimumOrderValue`: configured minimum net merchandise value in EUR (default: `250.00`).

**Scope note:** the minimum order value settings are currently configuration only. This implementation enforces the product minimum-quantity and quantity-step rules; it does not yet calculate or enforce a minimum cart/order value. Do not advertise that setting as an implemented restriction.

## Release checklist

- [ ] Run all syntax, XML, container, and PHPUnit checks above.
- [ ] Confirm the plugin can be refreshed, installed/updated, activated, and deactivated.
- [ ] Confirm the custom-field values persist in Administration.
- [ ] Pass Storefront and Store API acceptance scenarios.
- [ ] Verify English and German error messages.
- [ ] Review logs for service-container, translation, and cart-validation errors.
- [ ] Only then tag and publish a release.
