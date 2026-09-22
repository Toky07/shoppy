<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a published flag on products and store customer reviews.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE products ADD COLUMN published BOOLEAN NOT NULL DEFAULT 1');
        $this->connection->executeStatement('CREATE INDEX idx_products_published ON products (published)');
        $this->connection->executeStatement('CREATE TABLE product_reviews (
            id VARCHAR(36) NOT NULL,
            product_id VARCHAR(36) NOT NULL,
            author_id VARCHAR(36) NOT NULL,
            rating INTEGER NOT NULL,
            body CLOB NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_product_reviews_author ON product_reviews (product_id, author_id)');
        $this->connection->executeStatement('CREATE INDEX idx_product_reviews_product_id ON product_reviews (product_id)');
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('DROP TABLE product_reviews');
        $this->connection->executeStatement('DROP INDEX idx_products_published');
        $this->connection->executeStatement('ALTER TABLE products DROP COLUMN published');
    }
}
