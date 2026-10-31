# B2B Cart Rules

Shopware 6.7 plugin for configurable B2B product quantity restrictions.

## Implemented scope

- **SW-101 — Plugin skeleton:** plugin metadata, configuration and service discovery.
- **SW-102 — Product fields:** optional `rami_b2b_minimum_order_quantity` and `rami_b2b_order_quantity_step` custom fields.
- **SW-103 — Validation service:** validates positive quantity, minimum, quantity multiples, and malformed rule configuration.
- **SW-104 — Cart integration:** cart validator loads product custom fields and aggregates quantities by product ID.
- **SW-105 — Checkout enforcement:** invalid quantity rules create `GenericCartError` errors with `blockOrder = true`.
- **SW-106 — Translations:** English (`en-GB`) and German (`de-DE`) cart error catalogues.
- **SW-107 — Unit tests:** coverage for optional rules, minimums, multiples, combined violations, numeric strings, invalid configuration, and non-positive quantity.
- **SW-108 — Documentation:** install/update commands and acceptance checklist.
- **SW-109 — Minimum order value:** validates the configured merchandise position total before shipping costs, blocks invalid checkout through Shopware cart errors, and includes German/English messages plus unit tests.

## Requirements

- Shopware 6.7.13 or newer for declarative `custom-fields.xml`.
- PHP 8.3+ as declared by this plugin package.
- Plugin path: `custom/plugins/B2BCartRules`.

The project root `composer.json` must map the plugin namespace to `custom/plugins/B2BCartRules/src/`. This mapping is included in the project composer configuration on this branch.

## Install or update in Docker

Run from the Shopware project root (Compose service: `web`).

Fresh installation:

```bash
docker compose exec web php bin/console plugin:refresh
docker compose exec web php bin/console plugin:install --activate B2BCartRules
docker compose exec web composer dump-autoload
docker compose exec web php bin/console cache:clear
docker compose exec web php bin/console plugin:list
```

Existing installation:

```bash
docker compose exec web php bin/console plugin:update B2BCartRules
docker compose exec web composer dump-autoload
docker compose exec web php bin/console cache:clear
```

## Automated and configuration checks

```bash
docker compose exec web php -l custom/plugins/B2BCartRules/src/Service/ProductOrderRuleValidator.php
docker compose exec web php -l custom/plugins/B2BCartRules/src/Service/ProductOrderRuleCartValidator.php
docker compose exec web php -l custom/plugins/B2BCartRules/tests/Service/ProductOrderRuleValidatorTest.php
docker compose exec web vendor/bin/phpunit custom/plugins/B2BCartRules/tests/Service/ProductOrderRuleValidatorTest.php
docker compose exec web vendor/bin/phpunit custom/plugins/B2BCartRules/tests/Service/MinimumOrderValueValidatorTest.php
docker compose exec web xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/config.xml
docker compose exec web xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/services.xml
docker compose exec web xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/custom-fields.xml
docker compose exec web php bin/console cache:clear
docker compose exec web php bin/console debug:container "Rami\B2BCartRules\Service\ProductOrderRuleCartValidator"
```

These are verification commands, not a claim that all checks have already passed.

## Product setup and expected behavior

In Administration, open a product and set its custom fields. For example, set minimum quantity to `6` and quantity step to `6`.

| Quantity | Expected result |
|---:|---|
| 5 | Rejected: below minimum |
| 6 | Accepted |
| 7 | Rejected: not a multiple of 6 |
| 12 | Accepted |

Empty custom fields do not restrict quantity. Malformed rule values produce a violation rather than silently disabling the rule.

## Checkout acceptance tests

Run against a non-production sales channel with a test product:

1. Set minimum and step to `6`.
2. Add quantity `5`; verify an error appears and order submission is blocked.
3. Attempt Store API order submission with an invalid cart; verify no order is created.
4. Change quantity to `6` or `12`; verify the error disappears and valid checkout can proceed.
5. Set `B2BCartRules.config.enabled` to `false`; verify custom quantity rules no longer block checkout.
6. Repeat with quantity `7` and with both English and German storefront languages.
7. Confirm errors disappear after cart recalculation when the customer corrects the quantity.
8. Set the minimum order value to `250.00`; verify a merchandise total of `249.99` is blocked and `250.00` is accepted.
9. Disable `minimumOrderEnabled`; verify the minimum-value rule no longer blocks checkout while quantity rules still apply.
10. Repeat minimum-value checks through both Storefront checkout and Store API order submission.

These are acceptance scenarios and must be executed locally before declaring checkout behavior verified.

## Global configuration

- `B2BCartRules.config.enabled`: enable/disable product quantity restrictions (default `true`).
- `B2BCartRules.config.minimumOrderEnabled`: enable/disable the minimum merchandise value rule.
- `B2BCartRules.config.minimumOrderValue`: configured minimum merchandise value in the sales channel's currency context (default `250.00`). The cart position total is used, excluding shipping costs.

Storefront cart pages display the translated blocking error through Shopware's native cart error rendering. Store API cart validation also receives the blocking error, so invalid orders are rejected server-side. The minimum-order threshold uses Shopware's calculated merchandise position total and excludes shipping costs.

## Release checklist

- [ ] Run syntax, XML, PHPUnit, and container checks.
- [ ] Confirm plugin refresh/install/update/activation works.
- [ ] Confirm product custom fields persist in Administration.
- [ ] Pass Storefront and Store API acceptance scenarios.
- [ ] Verify English and German messages.
- [ ] Review application logs before tagging a release.
