<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store invoices issued when a payment is completed.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql(<<<'SQL'
            CREATE TABLE invoices (
              id VARCHAR(36) NOT NULL,
              number VARCHAR(32) NOT NULL,
              year INT NOT NULL,
              sequence INT NOT NULL,
              order_id VARCHAR(36) NOT NULL,
              customer_id VARCHAR(36) NOT NULL,
              payment_id VARCHAR(36) NOT NULL,
              amount_cents INT NOT NULL,
              issued_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_invoices_number ON invoices (number)');
        $this->addSql('CREATE UNIQUE INDEX uniq_invoices_order_id ON invoices (order_id)');
        $this->addSql('CREATE INDEX idx_invoices_year_sequence ON invoices (year, sequence)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('DROP TABLE invoices');
    }
}
