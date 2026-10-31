# Monitoring and incident runbook

## Structured logs

Search for these events:

- ERP order export succeeded
- ERP order export failed
- Duplicate export message ignored
- Export message skipped because another worker owns the record

Useful context includes orderId, exportId, correlationId, httpStatus and exceptionClass. Do not log bearer tokens, payment data, addresses or full customer payloads.

## Commands

    bin/console erp:orders:health
    bin/console erp:orders:retry <shopware-order-uuid>
    bin/console messenger:consume async --time-limit=3600 --memory-limit=256M

The health command reports counts by status, processing rows older than ten minutes, and retries whose retry time is due. A non-zero exit code means investigation is needed. The command does not inspect RabbitMQ queue depth, so monitor the broker and worker separately.

## Inspect logs

    tail -f var/log/dev.log
    grep -E 'ERP order export|Duplicate export|another worker' var/log/*.log
    docker compose logs -f web

Use docker compose ps to confirm the actual PHP service name. For a persistent worker, use a process supervisor in production rather than a shell session.

## Failure runbook

1. Run erp:orders:health.
2. Search application logs by order UUID or correlation ID.
3. Verify the ERP URL in plugin configuration.
4. Check network access from the PHP container and ERP service health.
5. Check whether the ERP accepted the request before manually retrying.
6. Retry with erp:orders:retry only after understanding the cause.
7. Confirm the persisted status and ERP reference.

## Operational caveats

A timeout does not prove that the ERP did not process the request. Production deployment should add an ERP-side idempotency key, alert routing, log retention, retry backoff and a reviewed outbox design.