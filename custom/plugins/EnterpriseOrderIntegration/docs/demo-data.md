# Demo data and ERP simulator

The standalone simulator is a development tool and must never be exposed publicly.

## Start the simulator

From the repository root, run the success scenario:

    php -S 0.0.0.0:8080 custom/plugins/EnterpriseOrderIntegration/tools/erp-simulator/router.php

To simulate an outage, start it in a separate terminal with an environment variable:

    ERP_SIMULATOR_SCENARIO=unavailable php -S 0.0.0.0:8080 custom/plugins/EnterpriseOrderIntegration/tools/erp-simulator/router.php

Supported values are success, unauthorized, invalid, unavailable and timeout. The timeout scenario deliberately waits 15 seconds.

Configure the plugin ERP base URL to a host reachable from the PHP container, for example http://host.docker.internal:8080 on Docker Desktop. Confirm reachability from inside the PHP container. The client appends /api/orders to the configured base URL.

## Demo scenarios

Create orders through Shopware's normal checkout and payment flow:

- one paid order with a single line item;
- one paid order with multiple line items;
- one order with pending payment that must not export;
- one cancelled transaction that must not export.

The simulator validates the basic payload contract and returns HTTP 201 with a deterministic reference for valid payloads. Failure modes return HTTP 401, 422 or 503, or simulate a timeout.

Use the generated Shopware order UUID with the health and retry commands. Never use real customer details in demo fixtures.