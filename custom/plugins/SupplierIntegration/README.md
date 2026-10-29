# SW-105 — Supplier Integration & Product Intelligence

Shopware 6 plugin training project. This branch contains the initial storefront fixture and the first integration backend increment.

## Architecture

- **DAL/source of truth:** supplier, supplier offer, sync run and per-item sync error entities.
- **Relations:** supplier 1:N offers; supplier 1:N sync runs; sync run 1:N errors.
- **Idempotence:** unique database key (supplier_id, external_id) and stable offer UUID derived from supplier ID + external ID. Re-delivering the same external offer updates the existing record rather than creating a duplicate.
- **API adapters:** demo fixture client for local work and a Symfony HTTP client for a paginated external API. Configure SUPPLIER_API_BASE_URL and optionally SUPPLIER_API_TOKEN for real API mode.
- **Async:** StartSupplierSyncMessage handler and Doctrine Messenger transport. Worker command: bin/console messenger:consume supplier_sync -vv. Failed messages are routed to the failed transport.
- **Search:** bulk OpenSearch HTTP adapter is included; configure OPENSEARCH_URL and optionally OPENSEARCH_INDEX. The DB remains canonical; the reindex command currently validates the DAL read path and is a starting point for a complete reindex pipeline.
- **Storefront:** /supplier-offers shows the bundled fictional demo data. This route is fixture-backed and does not yet query the DAL or search index.

## Install / validate

From the Shopware root:

    bin/console plugin:refresh
    bin/console plugin:install --activate SupplierIntegration
    bin/console database:migrate SupplierIntegration --all
    bin/console cache:clear
    bin/console plugin:list | grep SupplierIntegration

A supplier record must exist before running bin/console supplier-integration:sync <supplier-uuid>. The command creates a sync-run row and dispatches the message. If Messenger is configured with Doctrine transport, run a worker in a separate terminal:

    bin/console messenger:consume supplier_sync -vv

The demo API client reads src/Resources/demo/supplier-offers.json. The external API client expects paginated JSON shaped like {"items": [], "hasMore": false}; each item should include externalId, sku, name, and optional price, currency, stock, category, and deliveryDays.

## Important limitations

This is a substantial training scaffold, not yet production-ready. The Storefront page is still fixture-backed; there is no admin CRUD/monitoring UI; OpenSearch indexing is not yet wired into the import handler and the reindex command is only a DAL smoke-check; the integration tests have not been run against the local Docker environment. Validate Shopware-version compatibility and service configuration locally before relying on it. Never commit real credentials.
