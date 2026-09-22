<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add business SKUs, product variants, and the variant sold in a cart or order.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE products ADD COLUMN sku VARCHAR(40) DEFAULT NULL');

        $this->connection->executeStatement('CREATE TABLE product_variants (
            id VARCHAR(36) NOT NULL,
            product_id VARCHAR(36) NOT NULL,
            sku VARCHAR(40) NOT NULL,
            size VARCHAR(40) DEFAULT NULL,
            color VARCHAR(40) DEFAULT NULL,
            stock INTEGER NOT NULL,
            position INTEGER NOT NULL,
            PRIMARY KEY(id),
            CONSTRAINT fk_product_variants_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
        )');
        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_product_variants_sku ON product_variants (sku)');
        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_product_variants_options ON product_variants (product_id, size, color)');
        $this->connection->executeStatement('CREATE INDEX idx_product_variants_product_id ON product_variants (product_id)');

        /** @var list<array{id: string, slug: string, name: string, stock: int|string}> $products */
        $products = $this->connection->fetchAllAssociative('SELECT id, slug, name, stock FROM products');
        $used = [];

        foreach ($products as $product) {
            $sku = $this->uniqueSku($this->skuFromSlug((string) $product['slug']), $used);
            $this->connection->executeStatement(
                'UPDATE products SET sku = ? WHERE id = ?',
                [$sku, $product['id']],
            );
            $product['sku'] = $sku;
            $this->seedApparelVariants($product, $used);
        }

        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_products_sku ON products (sku)');
        $this->connection->executeStatement('ALTER TABLE cart_items ADD COLUMN variant_id VARCHAR(36) DEFAULT NULL');
        $this->connection->executeStatement('DROP INDEX uniq_cart_items_cart_product');
        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_cart_items_cart_product_variant ON cart_items (cart_id, product_id, variant_id)');
        $this->connection->executeStatement('ALTER TABLE order_items ADD COLUMN variant_id VARCHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE order_items DROP COLUMN variant_id');
        $this->connection->executeStatement('DROP INDEX uniq_cart_items_cart_product_variant');
        $this->connection->executeStatement('ALTER TABLE cart_items DROP COLUMN variant_id');
        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_cart_items_cart_product ON cart_items (cart_id, product_id)');
        $this->connection->executeStatement('DROP TABLE product_variants');
        $this->connection->executeStatement('DROP INDEX uniq_products_sku');
        $this->connection->executeStatement('ALTER TABLE products DROP COLUMN sku');
    }

    /**
     * @param array{id: string, slug: string, name: string, stock: int|string, sku?: string} $product
     * @param array<string, true> $used
     */
    private function seedApparelVariants(array $product, array &$used): void
    {
        $name = (string) $product['name'];

        if (!str_starts_with($name, 'T-shirt ')) {
            return;
        }

        $color = trim(substr($name, strlen('T-shirt ')));
        $stock = (int) $product['stock'];
        $each = intdiv($stock, 3);
        $remainder = $stock - ($each * 3);
        $sku = (string) $product['sku'];

        foreach (['S', 'M', 'L'] as $position => $size) {
            $variantStock = $each + ($size === 'M' ? $remainder : 0);
            $variantSku = $this->uniqueSku($sku.'-'.$size, $used);
            $this->connection->executeStatement(
                'INSERT INTO product_variants (id, product_id, sku, size, color, stock, position) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $this->variantId((string) $product['id'], $size),
                    $product['id'],
                    $variantSku,
                    $size,
                    $color,
                    $variantStock,
                    $position,
                ],
            );
        }
    }

    private function skuFromSlug(string $slug): string
    {
        $sku = strtoupper($slug);
        $sku = preg_replace('/[^A-Z0-9]+/', '-', $sku) ?? $sku;
        $sku = trim($sku, '-');

        if ($sku === '') {
            $sku = 'PRODUIT';
        }

        if (strlen($sku) > 40) {
            $sku = rtrim(substr($sku, 0, 40), '-');
        }

        return $sku;
    }

    /**
     * @param array<string, true> $used
     */
    private function uniqueSku(string $sku, array &$used): string
    {
        $candidate = $sku;
        $copy = 2;

        while (isset($used[$candidate])) {
            $suffix = '-'.$copy;
            $candidate = rtrim(substr($sku, 0, 40 - strlen($suffix)), '-').$suffix;
            ++$copy;
        }

        $used[$candidate] = true;

        return $candidate;
    }

    private function variantId(string $productId, string $size): string
    {
        $hex = substr(sha1($productId.$size), 0, 32);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
