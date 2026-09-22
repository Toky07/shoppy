<?php

declare(strict_types=1);

namespace App\Product\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final class ProductVariantHttpList
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<array{id: string|null, sku: string, size: string|null, color: string|null, stock: int}>|null
     */
    public static function fromPayload(array $payload): ?array
    {
        if (!array_key_exists('variants', $payload)) {
            return null;
        }

        if (!is_array($payload['variants'])) {
            throw InvalidRequest::of('This value must be a list.', 'variants');
        }

        $variants = [];

        foreach ($payload['variants'] as $index => $row) {
            if (!is_array($row)) {
                throw InvalidRequest::of('This value must be an object.', 'variants');
            }

            $variants[] = self::row($row, $index);
        }

        return $variants;
    }

    /**
     * @param array<mixed> $row
     *
     * @return array{id: string|null, sku: string, size: string|null, color: string|null, stock: int}
     */
    private static function row(array $row, int $index): array
    {
        $field = 'variants.'.$index;

        if (!isset($row['sku']) || !is_string($row['sku']) || trim($row['sku']) === '') {
            throw InvalidRequest::of('This field is required.', $field.'.sku');
        }

        if (!isset($row['stock']) || !is_int($row['stock'])) {
            throw InvalidRequest::of('This value must be an integer.', $field.'.stock');
        }

        $id = null;
        if (array_key_exists('id', $row) && $row['id'] !== null) {
            if (!is_string($row['id']) || $row['id'] === '') {
                throw InvalidRequest::of('This value must be a string.', $field.'.id');
            }
            $id = $row['id'];
        }

        return [
            'id' => $id,
            'sku' => $row['sku'],
            'size' => self::label($row, 'size', $field),
            'color' => self::label($row, 'color', $field),
            'stock' => $row['stock'],
        ];
    }

    /**
     * @param array<mixed> $row
     */
    private static function label(array $row, string $key, string $field): ?string
    {
        if (!array_key_exists($key, $row) || $row[$key] === null) {
            return null;
        }

        if (!is_string($row[$key])) {
            throw InvalidRequest::of('This value must be a string.', $field.'.'.$key);
        }

        $value = trim($row[$key]);

        return $value === '' ? null : $value;
    }
}
