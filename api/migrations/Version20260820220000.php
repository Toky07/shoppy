<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the orders and order_items tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE orders (id VARCHAR(36) NOT NULL, customer_id VARCHAR(36) NOT NULL, status VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE order_items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, order_id VARCHAR(36) NOT NULL, product_id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, unit_price_cents INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, quantity INTEGER NOT NULL, position INTEGER NOT NULL, CONSTRAINT FK_order_items_order_id FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE)');
        $this->addSql('CREATE INDEX idx_order_items_order_id ON order_items (order_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
    }
}
