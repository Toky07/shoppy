<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820171915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the products table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE products (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, price_cents INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products');
    }
}
