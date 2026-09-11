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

        if (!$hasName && !$hasPrice && !$hasDescription) {
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

        return new self($name, $priceCents, $hasDescription, $description);
    }
}
