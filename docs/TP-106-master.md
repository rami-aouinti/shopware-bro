# TP-106 — Asynchronous supplier catalog import (master-based)

## Start the worker

Configure `MESSENGER_TRANSPORT_DSN` in the local environment. Example for a local AMQP service:

```dotenv
MESSENGER_TRANSPORT_DSN=amqp://guest:guest@queue:5672/%2f
```

The hostname must match the AMQP service name reachable from the Shopware PHP container. Never commit real credentials.

```bash
bin/console messenger:consume async -vv --time-limit=3600 --memory-limit=256M
```

## Dispatch a JSON file

Create a JSON file containing a non-empty list of rows:

```json
[
  {
    "supplierCode": "ACME",
    "externalProductId": "ACME-100",
    "productId": null
  }
]
```

Queue the import:

```bash
bin/console techparts:erp:dispatch-supplier-import /tmp/supplier-catalog.json --source=erp-acme --batch-size=100
```

The command creates an import job and dispatches a Messenger message. The handler reuses the existing supplier import service and updates the job tracker when processing completes.

## Retry and failure behavior

The async transport retries up to five times with exponential delay (starting at 1 second, multiplier 2, capped at 60 seconds). After retries are exhausted, the message is sent to the AMQP `failed` transport. Depending on broker configuration, inspect the queue in the broker management UI; configure a separate operational retry/replay procedure.

## Important limitations

- The message currently contains a bounded JSON payload. Do not enqueue entire large files; store batches durably and enqueue a batch ID for production workloads.
- Retried messages can be delivered more than once. Ensure idempotent persistence and reconcile jobs when a message is moved to the failed queue.
- The existing supplier key-to-ID derivation should be reviewed before production.
- This feature has been added on a branch based on `master`; run PHP lint, Shopware container compilation, migrations and a real worker/broker test before merging.
