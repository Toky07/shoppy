<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the shop invoice logo and snapshot it on issued invoices.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('ALTER TABLE invoices ADD logo_relative_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoices ADD logo_mime_type VARCHAR(32) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            CREATE TABLE invoice_branding (
              id VARCHAR(16) NOT NULL,
              logo_relative_path VARCHAR(255) DEFAULT NULL,
              logo_mime_type VARCHAR(32) DEFAULT NULL,
              PRIMARY KEY (id)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('DROP TABLE invoice_branding');
        $this->addSql('ALTER TABLE invoices DROP logo_relative_path');
        $this->addSql('ALTER TABLE invoices DROP logo_mime_type');
    }
}
