# TP-108 — ERP inventory snapshot synchronization

## Scope

- Validate external stock snapshots before persistence.
- Apply a safety-stock value per snapshot; sellable stock is max(0, availableQuantity - safetyStock).
- Update Shopware's product stock field.
- Record external events in tp_inventory_sync and enforce idempotency with a unique key on (source, external_event_id).
- Persist the event log and product update in one database transaction. If the product update fails, the transaction rolls back and the event can be retried.
- Ignore a repeated event and log the duplicate without applying stock a second time.

## Example

An ERP sends availableQuantity = 25 and safetyStock = 5; Shopware receives stock = 20. If the ERP sends a quantity below the safety stock, Shopware receives zero, never a negative value.

## Important assumptions

- This first version treats incoming values as authoritative snapshots, not additive deltas. A repeated delivery of the same event is safe.
- eventId must be stable across retries and unique within a source. Do not generate a new event ID for each retry.
- This ticket does not yet enforce ordering between different event IDs for the same product. If the ERP can deliver snapshots out of order, the next iteration should include a monotonic source version or occurredAt watermark per product/source.
- The source and safety-stock policy currently arrive with the application call. A later admin configuration ticket can persist per-supplier safety-stock rules.
- The event table is an integration log and does not expose a Shopware DAL entity yet.

## Verification

1. Run bin/console database:migrate --all TechPartsErpConnector.
2. Run bin/console lint:container.
3. Run vendor/bin/phpunit custom/plugins/TechPartsErpConnector/tests/Unit/StockSnapshotTest.php.
4. With a valid product UUID, synchronize an event and verify the product stock and tp_inventory_sync row.
5. Submit the same source/event ID again and verify the result is duplicate and product stock is unchanged.
6. Force the product update to fail and verify the event row rolls back so the message can retry.

Runtime tests are still required in the project Docker environment.

## Dispatching a snapshot

Queue one snapshot through Messenger (the worker must be running):

```bash
bin/console techparts:inventory:dispatch-snapshot <PRODUCT_UUID> <ERP_EVENT_ID> <AVAILABLE_QUANTITY> --source=ACME_ERP --safety-stock=5
```

Use the same ERP event ID when retrying the same event. For example, an available quantity of 25 with safety stock 5 applies a Shopware stock of 20. The command queues work; the worker performs validation and persistence asynchronously.
