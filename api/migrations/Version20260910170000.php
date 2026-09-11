<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260910170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payments table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE payments (id VARCHAR(36) NOT NULL, order_id VARCHAR(36) NOT NULL, customer_id VARCHAR(36) NOT NULL, amount_cents INTEGER NOT NULL, status VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_payments_order_id ON payments (order_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE payments');
    }
}
