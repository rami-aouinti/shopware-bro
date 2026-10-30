<?php

declare(strict_types=1);

namespace TechPartsErpConnector\Application\Import;

final readonly class SupplierImportResult
{
    /**
     * @param list<array{row:int,error:string}> $errors
     */
    public function __construct(public int $received, public int $accepted, public int $rejected, public array $errors)
    {
    }
}
