<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index product price, creation date and name for catalog sorting.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_products_price_cents ON products (price_cents)');
        $this->addSql('CREATE INDEX idx_products_created_at ON products (created_at)');
        $this->addSql('CREATE INDEX idx_products_name ON products (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_products_price_cents');
        $this->addSql('DROP INDEX idx_products_created_at');
        $this->addSql('DROP INDEX idx_products_name');
    }
}
