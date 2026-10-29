# Product Manufacturer Reference (SW-101)

Shopware 6 plugin that adds an optional manufacturer reference to products through a declarative custom-field set.

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

Open the administration and edit a product. The field set is associated with products and the optional field is labelled **Manufacturer reference** (English) / **Herstellerreferenz** (German).

## Storage

The value is stored in Shopware's product `custom_fields` JSON data using the key `rami_manufacturer_reference`. This plugin does not introduce a dedicated `product.manufacturer_reference` database column.

## Validation

Run the self-contained definition check from the plugin directory:

```bash
php tests/validate-custom-fields.php
```

The check validates XML syntax and the expected field-set name, product association, field name, text type, and optional setting. It does not replace an end-to-end test against a running Shopware installation.

## Manual acceptance tests

1. Open **Catalogues / Kataloge → Products / Produkte** and edit a product.
2. Find the custom fields section and enter a value such as `ACME-REF-8472`.
3. Save, reload the product, and confirm the value persists.
4. Change the value and confirm the new value persists.
5. Leave the field empty and save; the product should still save.
6. Set a different value on another product and confirm the values are independent.

## Scope and limitations

This is a simple optional text field. It does not enforce uniqueness, validate a manufacturer's external catalogue, or provide dedicated filtering/indexing. If those become requirements, the data model and implementation should be reconsidered.
