# ERP Connector — one branch, eight acceptance gates

Branch: `feature/erp-connector-hardening-roadmap` (created from `master`).

This document is a test-first delivery checklist. A checked **implementation** is not a passed integration test. **Do not merge** until all required gates are verified with a real Shopware database and broker.

## Before any test

```bash
git fetch origin
git switch --track origin/feature/erp-connector-hardening-roadmap
docker compose up -d
docker compose exec web bin/console cache:clear
docker compose exec web bin/console database:migrate --all
docker compose exec web composer test:plugin
```

Because the branch adds a nullable `event_version` column to `tp_inventory_sync`, apply the migration before testing inventory synchronization.

### P0.1 — Supplier ID consistency (implemented; database verification pending)

`SupplierReferenceId::fromReference()` is now shared by `RegisterSupplierReferenceHandler` and `SupplierImportService`. The deterministic algorithm preserves the previous importer hash.

Run unit tests:

```bash
docker compose exec web vendor/bin/phpunit -c phpunit.dist.xml custom/plugins/TechPartsErpConnector/tests/Unit/SupplierReferenceIdTest.php
```

Database acceptance: import the same supplier/reference twice, through the same path and through different paths. Verify `tp_supplier_catalog` contains exactly one corresponding record.

**Legacy data warning:** Rows previously written by the old direct handler may have different IDs. Audit and reconcile any pre-existing supplier IDs before deploying this change to a populated database.

### P0.2 — Messenger (configuration already existed on master; live worker test pending)

The existing `config/packages/messenger.yaml` routes both ERP messages to `async` and defines `failed` (Doctrine), retries: 5, delay: 1000 ms, multiplier: 2, max: 60000 ms. `compose.yaml` already defines a LavinMQ broker using AMQP.

```bash
docker compose ps queue
docker compose exec web bin/console debug:messenger
docker compose exec web bin/console messenger:stats
docker compose exec web bin/console messenger:consume async -vv --limit=1 --time-limit=60
```

In a separate shell, dispatch a valid inventory event with an existing product UUID:

```bash
docker compose exec web bin/console techparts:inventory:dispatch-snapshot PRODUCT_UUID erp-event-100 25 --source=ACME_ERP --safety-stock=5 --erp-version=100
```

Verify a worker receives it, the product stock becomes 20, and `tp_inventory_sync` records `applied`. Repeat the event: stock should not change. For retry/failure testing, use a controlled broken ERP event in a disposable environment; inspect `messenger:failed:show` and `messenger:failed:retry` and avoid injecting deliberate failures into production.

### P0.3 — Database/DAL integration (partial implementation; live verification pending)

A rollback-only DBAL integration test is now present at `tests/Integration/InventoryPersistenceIntegrationTest.php`. It checks uniqueness of (source, event ID) and independence of different sources, using an explicit DB transaction. Run **only against an isolated, disposable database**, after migrations:

```bash
docker compose exec web composer test:erp:integration
```

A dedicated Shopware Kernel/DAL repository test, product stock update + rollback test, and concurrent version-check integration test remain open. The existing DBAL test does **not** yet verify Shopware DAL or real Messenger handlers. PHPUnit unit successes do **not** cover these.

### P1.1 — Local ERP mock (implemented; HTTP smoke pending)

The optional mock service is isolated in `compose.erp-mock.yaml`; it is **not** started by default.

```bash
docker compose -f compose.yaml -f compose.override.yaml -f compose.erp-mock.yaml up -d erp-mock
docker compose exec web curl -fsS -H "Authorization: Bearer local-mock-token" http://erp-mock:8090/health
docker compose exec web curl -fsS -H "Authorization: Bearer local-mock-token" http://erp-mock:8090/api/suppliers
docker compose exec web curl -i -H "Authorization: Bearer local-mock-token" http://erp-mock:8090/api/simulate/error
```

Mock endpoints: `/health`, `/api/suppliers`, `/api/stock`, `/api/simulate/error`. Current production HTTP adapter uses `/health`; catalog and stock automatic pull from these endpoints is **not yet implemented**. For the HTTP client unit tests:

```bash
docker compose exec web vendor/bin/phpunit -c phpunit.dist.xml custom/plugins/TechPartsErpConnector/tests/Unit/ErpHttpClientTest.php
```

If testing the actual `ErpGateway`, configure `TP_ERP_BASE_URL=http://erp-mock:8090`, `TP_ERP_API_TOKEN=local-mock-token`, and `TP_ERP_TIMEOUT=5` **in local, non-committed settings**; rebuild/clear the container config as needed. Never commit real credentials.

### P1.2 — Out-of-order inventory events (implementation started; integration verification pending)

An optional `--erp-version` is carried through CLI, Messenger, and `StockSnapshot`. The transactional service locks the product row, checks the greatest **applied** version for the same source/product, and ignores older events. Once an ordered stream exists, unversioned events for that product/source are ignored.

```bash
# Use an existing Shopware product UUID and a dedicated test source.
docker compose exec web bin/console techparts:inventory:dispatch-snapshot PRODUCT_UUID event-200 30 --source=ACME_ERP --erp-version=200
docker compose exec web bin/console techparts:inventory:dispatch-snapshot PRODUCT_UUID event-199 10 --source=ACME_ERP --erp-version=199
```

Consume each message with `messenger:consume async --limit=1` and verify stock stays at 30. Test two concurrently arriving versions and a mixed versioned/unversioned stream. **Caution:** source-specific sequencing does not define precedence among multiple ERP sources updating the same Shopware stock. Add a business priority/ownership rule before supporting multi-source writes.

### P1.3 — Large imports (not implemented)

Current `SyncSupplierCatalogMessage` carries all JSON rows, and the handler finalizes the job immediately after its own message. Simply splitting one job into many messages would corrupt the job's counters/status. Implement durable batches with a batch table, idempotent batch IDs, atomic claim/complete transitions, total counters and a finalizer. Test worker crashes and repeat delivery before rollout.

### P2.1 — Administration (partial implementation)

`src/Resources/config/config.xml` adds a Shopware-native Administration configuration toggle. `HttpErpGateway::health()` honors it. The toggle does **not** disable CLI imports or inventory sync; those controls and a read-only import history Administration view remain to build.

In Administration: Extensions > My extensions > TechParts ERP Connector > Configure. Disable the toggle, then verify `ErpGateway::health()` reports unavailable. API token and infrastructure secrets still belong in environment variables.

### P2.2 — Observability and CI (partly implemented)

The plugin already logs imports, duplicates and failures. The new `.github/workflows/erp-connector-quality.yml` runs PHP lint, PHPUnit unit tests and PHPStan for the ERP plugin on push/PR. Check its status on GitHub; it has **not** been executed by this assistant.

Remaining: dedicated structured event correlation IDs, redaction checks, alerts for failed jobs/stuck batches, metrics, DB-backed integration workflow and worker health monitoring.

## Suggested sequential acceptance

1. `SupplierReferenceIdTest` and repeated import against database.
2. Verify routing and start worker.
3. DAL/DBAL transaction, duplicate, rollback scenarios.
4. Mock HTTP authentication, success and 503 error.
5. Newer version then older version, under concurrency.
6. Durable batching / retry / finish job.
7. Admin configuration and history UI.
8. CI, logs, monitoring and production release checklist.

Only mark a step complete after its own test passes. Keep all work on **this single feature branch** and never fast-forward `master` before review.
