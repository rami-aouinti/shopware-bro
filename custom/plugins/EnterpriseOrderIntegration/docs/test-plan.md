# Test plan

## Unit tests

- ExportOrderMessage preserves order and correlation identifiers.
- Invalid order UUID is rejected before database work.
- Already-succeeded export messages are ignored.
- A successful ERP response stores succeeded status and the ERP reference.
- A non-2xx response stores failure details and is rethrown for Messenger retry handling.
- Transport timeout stores a failure and logs correlation metadata.
- Manual retry refuses succeeded exports.

## Integration scenarios

| Scenario | Expected result |
| --- | --- |
| Payment transaction enters paid | One export message is dispatched |
| Transaction enters a non-paid state | No export message is dispatched |
| Same payment event is delivered twice | One export row exists due to unique order ID |
| ERP returns 201 with reference | Status becomes succeeded |
| ERP returns 401, 422 or 503 | Status becomes failed and error is logged |
| ERP times out | Worker can retry; checkout request is not blocked by the HTTP call |
| Same message arrives after success | No second ERP call |
| Manual retry for succeeded export | Retry is refused |
| Export remains processing for more than 10 minutes | Health command exits non-zero |
| ERP credentials are missing | Export fails with a configuration error; no secret is logged |

## Local validation

Run commands in the Shopware PHP container:

    php -l custom/plugins/EnterpriseOrderIntegration/src/Subscriber/PaymentStateSubscriber.php
    php -l custom/plugins/EnterpriseOrderIntegration/src/MessageHandler/ExportOrderHandler.php
    php -l custom/plugins/EnterpriseOrderIntegration/src/Command/OrderExportHealthCommand.php
    php -l custom/plugins/EnterpriseOrderIntegration/src/Command/OrderExportRetryCommand.php
    vendor/bin/phpunit custom/plugins/EnterpriseOrderIntegration/tests
    bin/console lint:container
    bin/console plugin:refresh

## Acceptance gate

Do not mark the project complete until the migration is applied, the plugin can be installed and activated, the worker is configured, the positive and negative scenarios pass, and the logs/health command are inspected in the running Docker environment.