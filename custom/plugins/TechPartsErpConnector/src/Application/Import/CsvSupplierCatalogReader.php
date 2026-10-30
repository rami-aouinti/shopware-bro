<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

final readonly class CsvSupplierCatalogReader
{
    /**
     * Expected headers: supplier_code, external_product_id; product_id is optional.
     *
     * @return iterable<SupplierImportRow>
     */
    public function read(string $path, string $delimiter = ','): iterable
    {
        if (strlen($delimiter) !== 1) {
            throw new \InvalidArgumentException('CSV delimiter must be exactly one character');
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException(sprintf('Cannot open CSV file: %s', $path));
        }

        try {
            $headers = fgetcsv($handle, 0, $delimiter);
            if ($headers === false) {
                throw new \RuntimeException('CSV file is empty or has no header row');
            }

            $headers = array_map(static function (mixed $header): string {
                $value = trim((string) $header);

                return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
            }, $headers);
            $headers = array_map(static fn (string $header): string => strtolower($header), $headers);
            $supplierIndex = array_search('supplier_code', $headers, true);
            $externalIdIndex = array_search('external_product_id', $headers, true);
            $productIdIndex = array_search('product_id', $headers, true);

            if ($supplierIndex === false || $externalIdIndex === false) {
                throw new \RuntimeException('CSV headers must contain supplier_code and external_product_id');
            }

            while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
                if ($values === [null]) {
                    continue;
                }

                $supplierCode = trim((string) ($values[$supplierIndex] ?? ''));
                $externalProductId = trim((string) ($values[$externalIdIndex] ?? ''));
                $productId = $productIdIndex === false ? null : trim((string) ($values[$productIdIndex] ?? ''));

                yield new SupplierImportRow(
                    $supplierCode,
                    $externalProductId,
                    $productId === '' ? null : $productId,
                );
            }
        } finally {
            fclose($handle);
        }
    }
}
