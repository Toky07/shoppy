<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Request;

use App\Cart\Application\Command\MergeCartLine;
use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class MergeCartHttpRequest
{
    /**
     * @param list<MergeCartLine> $items
     */
    public function __construct(public array $items)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['items']) || !is_array($payload['items'])) {
            throw InvalidRequest::of('This field is required.', 'items');
        }

        $items = [];

        foreach ($payload['items'] as $index => $item) {
            if (!is_array($item)) {
                throw InvalidRequest::of('This value must be an object.', 'items.'.$index);
            }

            $items[] = new MergeCartLine(
                self::productId($item, $index),
                self::quantity($item, $index),
                AddToCartHttpRequest::variantId($item),
            );
        }

        return new self($items);
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function productId(array $item, int $index): string
    {
        if (!isset($item['productId']) || !is_string($item['productId'])) {
            throw InvalidRequest::of('This field is required.', 'items.'.$index.'.productId');
        }

        return $item['productId'];
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function quantity(array $item, int $index): int
    {
        if (!isset($item['quantity']) || !is_int($item['quantity'])) {
            throw InvalidRequest::of('This field is required.', 'items.'.$index.'.quantity');
        }

        return $item['quantity'];
    }
}
