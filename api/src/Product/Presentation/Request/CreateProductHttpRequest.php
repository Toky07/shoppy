<?php

declare(strict_types=1);

namespace App\Product\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class CreateProductHttpRequest
{
    public function __construct(
        public string $name,
        public int $priceCents,
        public ?string $description,
        public int $stock,
        public ?string $categoryId,
        public ?string $sku,
        /** @var list<array{id: string|null, sku: string, size: string|null, color: string|null, stock: int}>|null */
        public ?array $variants,
        public bool $published,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['name']) || !is_string($payload['name'])) {
            throw InvalidRequest::of('This field is required.', 'name');
        }

        if (!isset($payload['priceCents']) || !is_int($payload['priceCents'])) {
            throw InvalidRequest::of('This field is required.', 'priceCents');
        }

        $description = $payload['description'] ?? null;
        if ($description !== null && !is_string($description)) {
            throw InvalidRequest::of('This value must be a string.', 'description');
        }

        $stock = $payload['stock'] ?? 0;
        if (!is_int($stock)) {
            throw InvalidRequest::of('This value must be an integer.', 'stock');
        }

        $categoryId = null;
        if (array_key_exists('categoryId', $payload) && $payload['categoryId'] !== null) {
            if (!is_string($payload['categoryId']) || $payload['categoryId'] === '') {
                throw InvalidRequest::of('This value must be a string.', 'categoryId');
            }
            $categoryId = $payload['categoryId'];
        }

        $sku = null;
        if (array_key_exists('sku', $payload) && $payload['sku'] !== null) {
            if (!is_string($payload['sku']) || trim($payload['sku']) === '') {
                throw InvalidRequest::of('This value must be a string.', 'sku');
            }
            $sku = $payload['sku'];
        }

        $published = true;
        if (array_key_exists('published', $payload)) {
            if (!is_bool($payload['published'])) {
                throw InvalidRequest::of('This value must be a boolean.', 'published');
            }
            $published = $payload['published'];
        }

        return new self(
            $payload['name'],
            $payload['priceCents'],
            $description,
            $stock,
            $categoryId,
            $sku,
            ProductVariantHttpList::fromPayload($payload),
            $published,
        );
    }
}
