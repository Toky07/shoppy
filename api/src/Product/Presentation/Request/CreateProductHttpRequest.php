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

        return new self($payload['name'], $payload['priceCents'], $description, $stock);
    }
}
