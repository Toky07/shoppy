<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260910160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create carts and cart_items tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE carts (id VARCHAR(36) NOT NULL, customer_id VARCHAR(36) NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_carts_customer_id ON carts (customer_id)');
        $this->addSql('CREATE TABLE cart_items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cart_id VARCHAR(36) NOT NULL, product_id VARCHAR(36) NOT NULL, quantity INTEGER NOT NULL, position INTEGER NOT NULL, CONSTRAINT FK_cart_items_cart_id FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE)');
        $this->addSql('CREATE INDEX IDX_cart_items_cart_id ON cart_items (cart_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_cart_items_cart_product ON cart_items (cart_id, product_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE carts');
    }
}
