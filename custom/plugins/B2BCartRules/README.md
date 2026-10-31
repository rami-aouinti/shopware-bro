# B2B Cart Rules

A Shopware 6.7 plugin foundation for configurable B2B cart rules.

## Current scope

### SW-101 — Plugin skeleton

- Plugin metadata and main plugin class
- Standard Shopware plugin configuration
- Symfony service auto-discovery
- Installation and smoke-test instructions

### SW-102 — Product order rule fields

- Declares a product custom-field set through `src/Resources/config/custom-fields.xml`
- Adds `rami_b2b_minimum_order_quantity` (integer, minimum 1)
- Adds `rami_b2b_order_quantity_step` (integer, minimum 1)
- Provides English and German labels/help text
- Leaves both fields optional: an empty value means no restriction for that rule

Shopware 6.7.13 and newer synchronizes declarative custom fields during the plugin lifecycle. These fields are editable on product records in Administration once the plugin has been refreshed/installed or updated.

**Important:** SW-102 only defines product metadata. It does not yet validate cart quantities or block checkout. That behaviour belongs to subsequent tickets.

## Requirements

- Shopware 6.7.13 or newer for declarative `custom-fields.xml`
- PHP version compatible with the host Shopware project
- Plugin located under `custom/plugins/B2BCartRules`

## Install in Docker

Run these commands from the Shopware project root. This project's Compose service is named `web`.

```bash
docker compose exec web php bin/console plugin:refresh
docker compose exec web php bin/console plugin:install --activate B2BCartRules
docker compose exec web php bin/console cache:clear
docker compose exec web php bin/console plugin:list
```

If the plugin is already installed and you only need to enable it:

```bash
docker compose exec web php bin/console plugin:activate B2BCartRules
docker compose exec web php bin/console cache:clear
```

If the plugin was installed before adding the custom-field definition, run the plugin update command and clear the cache:

```bash
docker compose exec web php bin/console plugin:update B2BCartRules
docker compose exec web php bin/console cache:clear
```

## Basic checks

```bash
php -l custom/plugins/B2BCartRules/src/B2BCartRules.php
python3 -m json.tool custom/plugins/B2BCartRules/composer.json
xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/config.xml
xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/services.xml
xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/custom-fields.xml
```

## Manual Administration check

1. Open **Catalogues > Products** (menu labels can vary by language).
2. Edit a product.
3. Find the **Product order rules / Bestellregeln für Produkte** custom-field set.
4. Save a minimum quantity such as `6` and a quantity step such as `6`.
5. Confirm both values persist after reopening the product.

Expected meaning for the next validation ticket:

- Minimum quantity `6`: quantities below 6 will be invalid.
- Quantity step `6`: allowed quantities are multiples of 6 (6, 12, 18, …).
- Empty field: no restriction from that rule.
- These fields are configuration only until cart and checkout validation is implemented.

## Global plugin configuration

- `B2BCartRules.config.enabled`: enable or disable custom rules (default: `true`)
- `B2BCartRules.config.minimumOrderEnabled`: enable the minimum order value rule (default: `true`)
- `B2BCartRules.config.minimumOrderValue`: minimum net merchandise value in EUR (default: `250.00`)

The minimum order value is stored as a text configuration value so decimal amounts can be represented. Later validation code must parse it strictly and reject invalid or negative values.
