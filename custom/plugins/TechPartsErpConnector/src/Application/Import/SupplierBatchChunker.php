<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

final class SupplierBatchChunker
{
    /**
     * @param iterable<SupplierImportRow> $rows
     * @return iterable<list<array{supplierCode:string,externalProductId:string,productId:?string}>>
     */
    public function chunk(iterable $rows, int $batchSize): iterable
    {
        if ($batchSize < 1 || $batchSize > 1000) {
            throw new \InvalidArgumentException('Batch size must be between 1 and 1000');
        }

        $batch = [];
        foreach ($rows as $row) {
            $batch[] = [
                'supplierCode' => $row->supplierCode,
                'externalProductId' => $row->externalProductId,
                'productId' => $row->productId,
            ];
            if (count($batch) === $batchSize) {
                yield $batch;
                $batch = [];
            }
        }

        if ($batch !== []) {
            yield $batch;
        }
    }
}
