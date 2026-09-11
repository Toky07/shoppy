<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Csv;

use App\Product\Application\Csv\ProductCsvReader;
use App\Product\Application\Csv\ProductCsvRow;
use RuntimeException;

final class FileProductCsvReader implements ProductCsvReader
{
    public function read(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to read product CSV: '.$path);
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false || $header !== ['name', 'description', 'priceCents', 'stock', 'image']) {
                throw new RuntimeException('Product CSV must start with name,description,priceCents,stock,image.');
            }

            $rows = [];

            while (($data = fgetcsv($handle)) !== false) {
                if ($data === [null] || $data === []) {
                    continue;
                }

                $rows[] = $this->mapRow($data);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param list<string|null> $data
     */
    private function mapRow(array $data): ProductCsvRow
    {
        if (count($data) < 5) {
            throw new RuntimeException('Product CSV row is incomplete.');
        }

        $description = trim((string) $data[1]);
        $priceCents = filter_var($data[2], FILTER_VALIDATE_INT);
        $stock = filter_var($data[3], FILTER_VALIDATE_INT);

        if ($priceCents === false || $stock === false) {
            throw new RuntimeException('Product CSV priceCents and stock must be integers.');
        }

        return new ProductCsvRow(
            name: trim((string) $data[0]),
            description: $description === '' ? null : $description,
            priceCents: $priceCents,
            stock: $stock,
            image: trim((string) $data[4]),
        );
    }
}
