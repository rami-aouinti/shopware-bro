# SW-105 — Supplier Integration & Product Intelligence

A Shopware 6 plugin training project for importing supplier offers, processing imports asynchronously with Symfony Messenger, persisting records through the Shopware DAL, and making offers searchable through OpenSearch/Elasticsearch.

## Demo data is included

`src/Resources/demo/supplier-offers.json` contains eight realistic supplier offers with different stock levels, prices, categories and delivery times. These are explicitly fictional demo records, not live supplier data. They are intended to support local development without credentials or a paid external API.

The target user journey is to open a Supplier Offers storefront page and see the imported demo offers, then filter/search them. A local demo API endpoint and the storefront view will be implemented in subsequent steps of SW-105; this initial commit establishes the plugin scaffold and demo dataset.

## Planned architecture

- `Supplier` DAL entity: external supplier configuration and status.
- `SupplierOffer` DAL entity: normalized supplier offer, unique by supplier + external product ID.
- `SupplierSyncRun` DAL entity: import lifecycle and counters.
- `SupplierSyncError` DAL entity: per-item diagnostics.
- HTTP client adapter + DTO validation for external APIs.
- Symfony Messenger messages/handlers for fetch, import and index operations.
- OpenSearch/Elasticsearch adapter and reindex/reconciliation command.
- Storefront offers page showing demo/imported offers; Administration controls and sync monitoring.

## Development principles

- Shopware database/DAL is the source of truth; search is a derived index.
- Never overwrite native Shopware sales prices or stock automatically.
- Do not commit real API credentials; configure secrets through environment variables or Shopware system config.
- API failures, invalid records and indexing failures must be observable and retryable where appropriate.
- Keep import handlers idempotent and use database uniqueness constraints to prevent duplicates.

## Local installation (after implementation is completed)

From the Shopware project root:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate SupplierIntegration
bin/console database:migrate SupplierIntegration --all
bin/console cache:clear
```

Then build the Administration only if the plugin adds Administration JavaScript. The demo offers storefront route and import command will be documented once those components are added.

## Scope status

This branch is being developed incrementally. The scaffold and demo dataset do not yet constitute the completed integration platform; Messenger processing, DAL entities, API endpoint, search adapter, and storefront page remain implementation tasks.
