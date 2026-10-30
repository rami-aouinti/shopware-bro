# Stable supplier reference IDs

## Problem

The import previously passed the business key `SUPPLIER:external-id` to `Uuid::fromStringToHex()`. That helper expects a UUID-shaped string, not an arbitrary business key, so supplier imports could fail before the DAL upsert.

## Change

Supplier catalog IDs are now derived from SHA-256 over a versioned, namespaced business key:

```php
substr(hash('sha256', "techparts:supplier-reference:v1\\0" . $reference->key()), 0, 32)
```

The resulting 32-character hexadecimal value is suitable for Shopware's 16-byte hexadecimal ID format and remains deterministic for the same supplier code and external product ID. Different key namespaces and a version marker reduce accidental coupling to unrelated IDs.

## Operational notes

- This is an ID-generation fix; it does not replace a database-level unique constraint on `supplier_code + external_product_id`.
- Do not change the namespace/version after data has been written without a migration plan.
- Runtime validation and automated tests still need to be run in the Shopware container.
- SHA-256 truncated to 128 bits has a very low collision probability, but systems requiring formal collision handling should also enforce the natural key at the database level.
