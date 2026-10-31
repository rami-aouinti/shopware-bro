# Enterprise Order Integration

Shopware 6.7 plugin that asynchronously exports paid orders to an ERP API. This plugin is separate from TechPartsErpConnector.

## Current capabilities

- Reacts to a payment transaction entering the paid state and dispatches an asynchronous message.
- Stores export status, attempt count, correlation ID, ERP reference, last error and retry timestamp.
- Sends a compact order payload with bounded HTTP timeouts.
- Prevents duplicate export records using a unique order ID and skips already-successful records.
- Provides health and guarded manual-retry console commands.
- Provides a searchable, filterable, sortable and paginated Administration export list.
- Provides an explicit demo-data command. Demo rows are marked and cannot be retried.
- Writes structured log context without logging tokens or full customer payloads.

## Architecture

Payment state transition -> PaymentStateSubscriber -> ExportOrderMessage -> ExportOrderHandler -> enterprise_order_export -> ErpClient -> ERP API.

## Install

Run in the PHP container from the Shopware project root:

    bin/console plugin:refresh
    bin/console plugin:install --activate EnterpriseOrderIntegration
    bin/console database:migrate --all
    bin/console cache:clear
    bin/console administration:build

The Administration build is required to compile the plugin's custom monitoring page in a local development setup. Configure the ERP API base URL in Extensions > My Extensions > Enterprise Order Integration. The endpoint expected by the client is POST /api/orders. The target host must be reachable from the PHP container.

## Monitoring

    bin/console erp:orders:health
    bin/console erp:orders:retry <shopware-order-uuid>

### Demo data

After the plugin migration has run, execute:

    bin/console erp:orders:demo-data

The command creates up to five clearly marked demo export rows using existing Shopware orders that do not already have an export record. It does not create orders and does not call the ERP. If there are no eligible orders, it reports that no rows were created. Demo rows are excluded from manual retry to prevent accidental ERP calls.

The Administration list supports status and date filters, text search across order UUID, correlation ID, ERP reference and error text, sorting, page size and pagination. Search uses the SQL database because the export table is a relational operational log and is not currently indexed as a custom OpenSearch document. OpenSearch should only be added if a dedicated index and synchronization lifecycle are introduced; the UI does not depend on OpenSearch being enabled.

Follow application logs:

    tail -f var/log/dev.log
    grep -E 'ERP order export|Duplicate export' var/log/*.log
    docker compose logs -f web

If the Compose PHP service is not named web, use the service shown by docker compose ps.

## Validation checklist

    php -l custom/plugins/EnterpriseOrderIntegration/src/MessageHandler/ExportOrderHandler.php
    php -l custom/plugins/EnterpriseOrderIntegration/src/Controller/Administration/OrderExportController.php
    php -l custom/plugins/EnterpriseOrderIntegration/src/Command/OrderExportDemoDataCommand.php
    vendor/bin/phpunit custom/plugins/EnterpriseOrderIntegration/tests
    bin/console lint:container
    bin/console cache:clear

Run checks inside the project's PHP container. A branch is not considered tested until these commands have actually passed in the user's Shopware 6.7.15.1 environment.

## Data protection

Never commit real ERP credentials. Logs should contain identifiers, status codes, duration and exception class, but not bearer tokens, payment information, addresses or full customer payloads.

See docs/monitoring-and-runbook.md, docs/demo-data.md, docs/test-plan.md and docs/ticket-map.md.

## Remaining production-hardening items

The initial implementation still requires runtime verification against Shopware 6.7.15.1, integration tests, retry policy tests, and ERP-side idempotency support. Do not claim exactly-once delivery: network failures can happen after the ERP accepted a request. See docs/ticket-map.md for the ticket-by-ticket status.
