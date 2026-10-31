# Ticket map — Enterprise Order Integration

| Ticket | Scope | Current implementation |
| --- | --- | --- |
| SW-200 | Plugin bootstrap and structure | Plugin class, Composer metadata, service registration, README |
| SW-201 | Domain/application/infrastructure separation | Application message, subscriber, handler and ERP client are separated; further extraction of domain services remains advisable |
| SW-202 | ERP configuration | Admin plugin config for base URL and optional token |
| SW-203 | Persistence | Migration, unique order ID, status, attempts, correlation ID, ERP reference and retry timestamp |
| SW-204 | Quality foundation | Unit contract test and GitHub Actions PHP syntax workflow |
| SW-205 | Paid payment event | Subscriber resolves the order from the paid order transaction |
| SW-206 | Durable export initiation | Export record is created on first handling; full transactional outbox is not yet implemented |
| SW-207 | Asynchronous processing | Export message is routed to the existing async Messenger transport |
| SW-208 | Payload mapping and processing | Handler maps order details, calls ERP, persists state and logs outcome |
| SW-209 | ERP HTTP client | Bounded HTTP timeouts, bearer token support and correlation header |
| SW-210 | Idempotency and concurrency | Unique order row, successful-message guard and atomic claim for pending/failed rows; ERP-side idempotency key still recommended |
| SW-211 | ERP simulator | Local PHP simulator supports success, unauthorized, validation, unavailable and timeout scenarios |
| SW-212 | Retry behavior | Messenger retry strategy is inherited from the existing async transport; failure state and retry timestamp are persisted |
| SW-213 | Concurrency/recovery | Atomic record claim prevents two workers from simultaneously claiming a pending row; recovery for indefinitely processing records is not automated |
| SW-214 | Manual retry | CLI command rejects invalid IDs and successful exports |
| SW-215 | Monitoring API | Authenticated API list and retry endpoints with order ACLs |
| SW-216 | Administration UI | Admin navigation and grid with refresh and retry actions |
| SW-217 | Structured logs | Success, failure, duplicate and concurrency-skip events include correlation identifiers |
| SW-218 | Health and runbook | Health command, log queries and incident runbook |
| SW-219 | Demo fixtures | ERP success fixture and local simulator scenarios |
| SW-220 | Test coverage | Unit message contract test and detailed test matrix; integration tests remain to be added |
| SW-221 | End-to-end validation | Test scenarios documented; runtime execution is still required |
| SW-222 | Security/performance review | Token and payload redaction documented; production security review remains required |
| SW-223 | Release/docs | Setup, monitoring, demo and test documentation |

## Honest completion status

The feature implementation is committed on the feature branch, but it is not yet production-ready until it has been installed and exercised in the Shopware 6.7.15.1 Docker environment. In particular, verify event APIs, Administration asset compilation, ACL behavior, Messenger routing, migration and all integration scenarios. The current GitHub workflow checks PHP syntax; it does not replace Shopware integration tests.