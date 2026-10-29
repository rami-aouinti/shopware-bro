# Product Manufacturer Reference (SW-101 / SW-102)

Shopware 6 plugin that adds an optional manufacturer reference to products through a declarative custom-field set and displays it on the product detail page.

## Requirements

- Shopware 6.7.13 or newer (declarative custom fields are supported from this version).
- PHP version supported by the installed Shopware version.

## Installation

From the Shopware project root:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate ProductManufacturerReference
bin/console cache:clear
```

When the plugin is already installed and its custom-field definition changes, increment the plugin version in `composer.json`, then run:

```bash
bin/console plugin:refresh
bin/console plugin:update ProductManufacturerReference
bin/console cache:clear
```

Open the Administration and edit a product. The field set is associated with products and the optional field is labelled **Manufacturer reference** (English) / **Herstellerreferenz** (German).

## Storefront display (SW-102)

The plugin extends the core product description template and displays the reference underneath the description only when the value is non-empty. The label is translated using the plugin snippets for English (`en-GB`) and German (`de-DE`). Twig's default auto-escaping remains enabled.

After pulling the branch and refreshing the plugin, clear the cache:

```bash
bin/console plugin:refresh
bin/console cache:clear
```

If the plugin is already installed and Shopware does not pick up the template/snippets immediately, run `bin/console theme:compile` as well.

## Storage

The value is stored in Shopware's product `custom_fields` JSON data using the key `rami_manufacturer_reference`. This plugin does not introduce a dedicated `product.manufacturer_reference` database column, so a SQL migration is not needed for this field.

## Validation

From the plugin directory, run:

```bash
php tests/validate-custom-fields.php
php tests/validate-storefront-reference.php
```

The first check validates the declarative XML field definition. The second statically checks the storefront override, conditional rendering, translation keys, and snippet JSON. These checks do not replace visual acceptance testing against a running Shopware installation; the workflow runs both scripts on the SW-102 branch and pull requests touching the plugin.

## Manual acceptance tests

1. Open **Catalogues / Kataloge → Products / Produkte** and edit a test product.
2. Enter `MFR-TEST-001` in the Manufacturer reference field and save.
3. Open the product in the Storefront and verify **Manufacturer reference: MFR-TEST-001** appears underneath the description.
4. Clear the field, save, reload, and verify that neither the label nor the value appears.
5. If both storefront languages are configured, verify the English and German labels.
6. Confirm the original product description still renders.

## Scope and limitations

This is a simple optional text field. It does not enforce uniqueness, validate a manufacturer's external catalogue, or provide dedicated filtering/indexing. If those become requirements, the data model and implementation should be reconsidered.
