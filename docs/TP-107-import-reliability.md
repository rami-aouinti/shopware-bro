# TP-107 — Supplier import reliability

## Changes

- Keep supplier reference IDs deterministic and namespaced; the existing `uniq_tp_supplier_external` database key enforces the natural key `(supplier_code, external_product_id)`.
- Cap retained row-level validation details at 1,000 while preserving the total rejected count. This bounds memory usage for large invalid feeds.
- Add a terminal `ImportJobTracker::fail()` transition that records a bounded error summary and finish time without overwriting counters.
- Mark synchronous CSV import failures as failed when an exception escapes the importer.
- Mark dispatch failures as failed if Messenger cannot enqueue the message.
- Subscribe to Messenger's `WorkerMessageFailedEvent`; only mark the job failed when the retry strategy says the message will not be retried.

## Why the final-failure check matters

Marking a job failed on every `WorkerMessageFailedEvent` would incorrectly close it after the first transient exception, even though Messenger is about to retry it. The subscriber therefore checks `willRetry()` before updating the job.

## Current constraints / follow-up

- The JSON dispatch command still reads and serializes the full input list in memory. It is suitable for development-sized payloads, not unbounded production imports. The next import architecture step should stage files durably and enqueue bounded batch IDs.
- A synchronous CSV failure after one or more successful DAL batches can leave some rows upserted; deterministic IDs make a retry safe, but job counters do not yet track per-batch progress.
- No claim is made that the Shopware container, broker, or database was run from this change environment. Verify syntax, container compilation, migration state, and worker retry behavior in the local Docker environment.

## Manual verification

1. Run `bin/console lint:container`.
2. Run `bin/console list techparts`.
3. Import a valid CSV twice and verify row count does not increase.
4. Import a CSV with invalid supplier codes and verify the rejected count is correct while the stored error details remain bounded.
5. Stop the broker, run `techparts:erp:dispatch-supplier-import`, and verify the job reaches `failed` if dispatch itself throws.
6. Dispatch a valid message, temporarily make the handler fail, and verify the job remains `running` while Messenger retries and becomes `failed` only after retries are exhausted.
7. Restore dependencies and run the worker against the configured failure transport to verify final-failure event behavior.
