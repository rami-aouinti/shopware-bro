# Supplier catalog CSV import

The first CSV workflow imports supplier-to-product references into the existing tp_supplier_catalog entity. It intentionally reuses the current SupplierImportService, DAL repository and import-job tracker rather than creating a second persistence path.

## CSV format

UTF-8 CSV with a header row. Required columns are supplier_code and external_product_id. The optional product_id column contains the Shopware product UUID in 32-character hex or valid Shopware UUID form.

\`\`\`csv
supplier_code,external_product_id,product_id
ACME,ACME-10001,018f0f7c8e1b7a8b9c0d1e2f3a4b5c6d
CONTOSO,SKU-77,
\`\`\`

The parser streams records instead of loading the entire file into memory. Empty lines are skipped. Rows with missing/invalid business values are rejected by the existing domain model/import service and reported in the import job summary. A malformed/missing header aborts the import before processing rows.

## Run

From the Shopware application container, after the plugin is installed and activated:

\`\`\`bash
bin/console techparts:import-supplier-catalog-csv /var/www/html/var/import/supplier-catalog.csv
bin/console techparts:import-supplier-catalog-csv /var/www/html/var/import/supplier-catalog.csv --delimiter=';'
\`\`\`

Place the file in a controlled import directory. Do not accept arbitrary user-supplied filesystem paths from a public API. For large production imports, use a staged file plus a durable batch identifier and dispatch only the batch ID through Messenger instead of placing file contents in a message.

## Current limitations

- This command runs synchronously; the existing async Messenger workflow is a separate change and is not assumed to be merged.
- A database/runtime exception can leave the job in running; terminal failure tracking is a follow-up reliability task.
- The import service must use stable deterministic IDs and the database should enforce a unique key on (supplier_code, external_product_id). Review the related open PR before merging this feature.
- PDF invoice generation, invoice numbering, e-mail delivery and notifications are intentionally separate modules so their lifecycle and retry rules can be tested independently.
- This change has not been executed against a local Shopware container in this environment. Run bin/console lint:container, bin/console list techparts and the plugin test suite before merging.
