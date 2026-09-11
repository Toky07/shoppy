<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260909160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add an index to list customer orders newest first.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_orders_customer_created_at ON orders (customer_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_orders_customer_created_at');
    }
}
