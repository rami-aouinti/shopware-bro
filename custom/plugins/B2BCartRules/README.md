# B2B Cart Rules

A Shopware 6.7 plugin foundation for configurable B2B cart rules.

## Current scope (SW-101)

- Plugin metadata and main plugin class
- Standard Shopware plugin configuration
- Symfony service auto-discovery
- Installation and smoke-test instructions

**Important:** this ticket only provides the plugin skeleton and configuration. It does not yet enforce minimum order values or product quantity rules. Those behaviours will be implemented in subsequent tickets.

## Requirements

- Shopware 6.7
- PHP 8.3 or newer, matching the host Shopware project
- The plugin located under `custom/plugins/B2BCartRules`

## Install in Docker

Run these commands from the Shopware project root. Replace `shopware` with the actual Compose service name if yours differs.

```bash
docker compose exec shopware php bin/console plugin:refresh
docker compose exec shopware php bin/console plugin:install --activate B2BCartRules
docker compose exec shopware php bin/console cache:clear
docker compose exec shopware php bin/console plugin:list
```

If the plugin is already installed and you only need to enable it:

```bash
docker compose exec shopware php bin/console plugin:activate B2BCartRules
docker compose exec shopware php bin/console cache:clear
```

## Basic checks

```bash
php -l custom/plugins/B2BCartRules/src/B2BCartRules.php
python3 -m json.tool custom/plugins/B2BCartRules/composer.json
```

If `xmllint` is installed, validate the XML files:

```bash
xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/config.xml
xmllint --noout custom/plugins/B2BCartRules/src/Resources/config/services.xml
```

Then open the Shopware Administration, navigate to Extensions, and open the configuration for **B2B Cart Rules**. Confirm that the global switch, minimum-order switch, and minimum net order value are displayed and can be saved.

## Configuration keys

- `B2BCartRules.config.enabled`: enable or disable custom rules (default: `true`)
- `B2BCartRules.config.minimumOrderEnabled`: enable the minimum order value rule (default: `true`)
- `B2BCartRules.config.minimumOrderValue`: minimum net merchandise value in EUR (default: `250.00`)

The minimum order value is stored as a text configuration value in this initial skeleton so decimal amounts can be represented. Later validation code must parse it strictly and reject invalid or negative values.
