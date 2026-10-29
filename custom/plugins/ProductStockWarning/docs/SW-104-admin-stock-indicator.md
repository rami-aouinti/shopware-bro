# SW-104 - Administration stock indicator

## Behavior

The Administration product listing adds a Stock status column. It uses the existing ProductStockWarning configuration key.

- Stock 0: Out of stock.
- Stock from 1 through the configured threshold: Low stock.
- Stock above the threshold: In stock.
- Default threshold when configuration cannot be loaded: 5.
- German and English labels are provided.
- Existing Shopware core files are not modified.

## Local verification

1. Activate ProductStockWarning.
2. Build or refresh the Administration assets using the command supported by the local Shopware environment.
3. Open Catalogues > Products.
4. With threshold 5, verify stock 0, 3, 5 and 8.
5. Change threshold to 2 and refresh the listing. Stock 3 should show In stock.
6. Verify that product editing and existing columns continue to work.

## Automated validation

Run from the plugin directory:

    php tests/validate-admin-stock-indicator.php

The static check verifies required extension files, extension points, expected stock-status rules and translation snippets. It does not replace a real Administration build and visual QA.
