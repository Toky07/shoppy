<?php

declare(strict_types=1);

namespace App\Product\Application\Csv;

interface ProductCsvReader
{
    /**
     * @return list<ProductCsvRow>
     */
    public function read(string $path): array;
}
