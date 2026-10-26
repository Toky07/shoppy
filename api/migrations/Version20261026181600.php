<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026181600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index access tokens by user and expiry so session trim and purge can scan less.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('CREATE INDEX idx_access_tokens_user_id ON access_tokens (user_id)');
        $this->addSql('CREATE INDEX idx_access_tokens_expires_at ON access_tokens (expires_at)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('DROP INDEX idx_access_tokens_user_id');
        $this->addSql('DROP INDEX idx_access_tokens_expires_at');
    }
}
