<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260910153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stock quantity to products.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD COLUMN stock INTEGER DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP COLUMN stock');
    }
}
