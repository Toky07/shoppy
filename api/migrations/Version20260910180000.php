<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260910180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product image URL.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD COLUMN image_url VARCHAR(2048) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP COLUMN image_url');
    }
}
