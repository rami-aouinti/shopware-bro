# Product Stock Warning (SW-103)

Shopware 6.7 plugin that shows a translated low-stock message on the product detail page without changing Shopware core files.

## Behavior

- Shows a warning when the product stock is greater than zero and less than or equal to the configured threshold.
- Defaults to a threshold of **5** units.
- Does not show this warning when stock is zero or greater than the threshold.
- Preserves Shopware's original buy form by calling `parent()`.
- Includes German (`de-DE`) and English (`en-GB`) messages.

## Install / activate

From the Shopware project root:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate ProductStockWarning
bin/console cache:clear
```

If the plugin is already installed, activate it with:

```bash
bin/console plugin:activate ProductStockWarning
bin/console cache:clear
```

Open **Extensions → My extensions → Product Stock Warning → Configure** to change the stock threshold. The default is 5; the supported range is 1–1000.

## Static validation

Run from the plugin directory:

```bash
php tests/validate-stock-warning.php
```

This validates the Composer metadata, plugin entry point, XML configuration, Twig extension points, stock condition, and both translation files. It does not replace a visual test in a running Shopware storefront.

## Manual acceptance test

1. Activate the plugin and clear the cache.
2. Configure the threshold as 5.
3. Set a product's stock to 3, save it, and open the product detail page: the warning should show “Nur noch 3 Stück auf Lager.” in German.
4. Set stock to 8: the warning should disappear.
5. Set stock to 0: the low-stock warning should not show; Shopware's normal availability behavior should remain.
6. Switch the storefront language to English and verify the English text.
7. Change the configured threshold to 2 and confirm that stock 3 no longer triggers the warning.

## Technical notes

- Twig override: `storefront/component/buy-widget/buy-widget.html.twig`, block `buy_widget_buy_form`.
- Configuration key: `ProductStockWarning.config.stockThreshold`.
- The feature reads the product's stock and does not reserve stock, modify inventory, or change purchase rules.
