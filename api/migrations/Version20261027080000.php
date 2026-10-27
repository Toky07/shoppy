<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store reusable checkout addresses on the signed-in customer.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('CREATE TABLE address_books (customer_id VARCHAR(36) NOT NULL, PRIMARY KEY (customer_id))');
        $this->addSql('CREATE TABLE saved_addresses (id VARCHAR(36) NOT NULL, recipient VARCHAR(80) NOT NULL, line1 VARCHAR(120) NOT NULL, line2 VARCHAR(120) DEFAULT NULL, postal_code VARCHAR(12) NOT NULL, city VARCHAR(80) NOT NULL, country VARCHAR(2) NOT NULL, is_default BOOLEAN NOT NULL, position INT NOT NULL, customer_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5A113CEB9395C3F3 ON saved_addresses (customer_id)');
        $this->addSql('ALTER TABLE saved_addresses ADD CONSTRAINT FK_5A113CEB9395C3F3 FOREIGN KEY (customer_id) REFERENCES address_books (customer_id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('ALTER TABLE saved_addresses DROP CONSTRAINT FK_5A113CEB9395C3F3');
        $this->addSql('DROP TABLE saved_addresses');
        $this->addSql('DROP TABLE address_books');
    }
}
