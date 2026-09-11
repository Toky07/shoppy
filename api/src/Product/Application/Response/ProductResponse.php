<?php

declare(strict_types=1);

namespace App\Product\Application\Response;

use App\Product\Domain\Entity\Product;
use DateTimeInterface;

final readonly class ProductResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $priceCents,
        public string $currency,
        public string $createdAt,
        public ?string $description,
        public int $stock,
        public ?string $imageUrl,
    ) {
    }

    public static function fromProduct(Product $product): self
    {
        return new self(
            $product->id()->value(),
            $product->name()->value(),
            $product->price()->cents(),
            $product->price()->currency(),
            $product->createdAt()->format(DateTimeInterface::ATOM),
            $product->description()?->value(),
            $product->stock()->value(),
            $product->image()?->value(),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     description: string|null,
     *     price: array{cents: int, currency: string},
     *     stock: int,
     *     imageUrl: string|null,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => [
                'cents' => $this->priceCents,
                'currency' => $this->currency,
            ],
            'stock' => $this->stock,
            'imageUrl' => $this->imageUrl,
            'createdAt' => $this->createdAt,
        ];
    }
}
