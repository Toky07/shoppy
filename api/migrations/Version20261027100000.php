<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store an admin audit log of role, price, stock and payment changes.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('CREATE TABLE audit_log (id VARCHAR(36) NOT NULL, action VARCHAR(32) NOT NULL, actor_id VARCHAR(36) DEFAULT NULL, actor_email VARCHAR(255) DEFAULT NULL, subject_type VARCHAR(32) NOT NULL, subject_id VARCHAR(36) NOT NULL, metadata JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_audit_log_created_at ON audit_log (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('DROP TABLE audit_log');
    }
}
