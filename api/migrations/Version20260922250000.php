<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922250000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reserve product stock held in carts.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement(
            'ALTER TABLE products ADD COLUMN reserved_stock INTEGER NOT NULL DEFAULT 0',
        );
        $this->connection->executeStatement(
            'ALTER TABLE product_variants ADD COLUMN reserved_stock INTEGER NOT NULL DEFAULT 0',
        );
        $this->connection->executeStatement(
            'UPDATE products SET reserved_stock = (
                SELECT COALESCE(SUM(quantity), 0) FROM cart_items
                WHERE cart_items.product_id = products.id AND cart_items.variant_id IS NULL
            )',
        );
        $this->connection->executeStatement(
            'UPDATE product_variants SET reserved_stock = (
                SELECT COALESCE(SUM(quantity), 0) FROM cart_items
                WHERE cart_items.variant_id = product_variants.id
            )',
        );
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE products DROP COLUMN reserved_stock');
        $this->connection->executeStatement('ALTER TABLE product_variants DROP COLUMN reserved_stock');
    }
}
