<?php

declare(strict_types=1);

namespace App\Product\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class UpdateProductHttpRequest
{
    public function __construct(
        public ?string $name,
        public ?int $priceCents,
        public bool $descriptionProvided,
        public ?string $description,
        public bool $categoryProvided,
        public ?string $categoryId,
        public bool $skuProvided,
        public ?string $sku,
        public bool $variantsProvided,
        /** @var list<array{id: string|null, sku: string, size: string|null, color: string|null, stock: int}> */
        public array $variants,
        public bool $publishedProvided,
        public bool $published,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        $hasName = array_key_exists('name', $payload);
        $hasPrice = array_key_exists('priceCents', $payload);
        $hasDescription = array_key_exists('description', $payload);
        $hasCategory = array_key_exists('categoryId', $payload);
        $hasSku = array_key_exists('sku', $payload);
        $hasVariants = array_key_exists('variants', $payload);
        $hasPublished = array_key_exists('published', $payload);

        if (!$hasName && !$hasPrice && !$hasDescription && !$hasCategory && !$hasSku && !$hasVariants && !$hasPublished) {
            throw InvalidRequest::of('At least one field is required.');
        }

        $name = null;
        if ($hasName) {
            if (!is_string($payload['name'])) {
                throw InvalidRequest::of('This value must be a string.', 'name');
            }
            $name = $payload['name'];
        }

        $priceCents = null;
        if ($hasPrice) {
            if (!is_int($payload['priceCents'])) {
                throw InvalidRequest::of('This value must be an integer.', 'priceCents');
            }
            $priceCents = $payload['priceCents'];
        }

        $description = null;
        if ($hasDescription) {
            if ($payload['description'] !== null && !is_string($payload['description'])) {
                throw InvalidRequest::of('This value must be a string.', 'description');
            }
            $description = $payload['description'];
        }

        $categoryId = null;
        if ($hasCategory && $payload['categoryId'] !== null) {
            if (!is_string($payload['categoryId']) || $payload['categoryId'] === '') {
                throw InvalidRequest::of('This value must be a string.', 'categoryId');
            }
            $categoryId = $payload['categoryId'];
        }

        $sku = null;
        if ($hasSku) {
            if (!is_string($payload['sku']) || trim($payload['sku']) === '') {
                throw InvalidRequest::of('This value must be a string.', 'sku');
            }
            $sku = $payload['sku'];
        }

        $variants = ProductVariantHttpList::fromPayload($payload) ?? [];

        $published = true;
        if ($hasPublished) {
            if (!is_bool($payload['published'])) {
                throw InvalidRequest::of('This value must be a boolean.', 'published');
            }
            $published = $payload['published'];
        }

        return new self(
            $name,
            $priceCents,
            $hasDescription,
            $description,
            $hasCategory,
            $categoryId,
            $hasSku,
            $sku,
            $hasVariants,
            $variants,
            $hasPublished,
            $published,
        );
    }
}
