# Demo data and ERP simulator

The standalone simulator is a development tool and must never be exposed publicly.

## Start the simulator

From the repository root, run:

    php -S 0.0.0.0:8080 custom/plugins/EnterpriseOrderIntegration/tools/erp-simulator/router.php

Configure the plugin's ERP base URL to a host reachable from the PHP container. If the simulator runs on the host machine, do not assume localhost inside Docker points to the host; use the Docker host gateway or place the simulator on the same Compose network.

## Success scenario

Send a valid order payload to POST /api/orders. The simulator returns HTTP 201 and a deterministic reference for that order ID. Repeating the same order ID returns the same reference.

## Failure scenarios

Append a query parameter to the URL for deterministic error testing:

- scenario=unauthorized returns HTTP 401.
- scenario=invalid returns HTTP 422.
- scenario=unavailable returns HTTP 503.
- scenario=timeout delays the response to exercise client timeouts.

For example, configure the ERP URL with /?scenario=unavailable only if your simulator deployment routes that query through the endpoint; otherwise add the query parameter to the endpoint URL directly.

## Suggested demo orders

Use Shopware's normal order creation flow to create:
- one paid order with a single line item;
- one paid order with multiple line items;
- one order with a pending payment that must not export;
- one cancelled transaction that must not export.

Use the generated Shopware order UUID with the health and retry commands. Never use real customer details in demo fixtures.