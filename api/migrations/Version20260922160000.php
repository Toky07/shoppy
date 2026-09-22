<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add account verification, deletion and one-time tokens.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD email_verified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE TABLE account_tokens (token_hash VARCHAR(64) NOT NULL, user_id VARCHAR(36) NOT NULL, purpose VARCHAR(32) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, subject VARCHAR(255) DEFAULT NULL, consumed_at DATETIME DEFAULT NULL, PRIMARY KEY (token_hash))');
        $this->addSql('CREATE INDEX idx_account_tokens_user_purpose ON account_tokens (user_id, purpose)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE account_tokens');
        $this->addSql('ALTER TABLE users DROP email_verified_at');
        $this->addSql('ALTER TABLE users DROP deleted_at');
    }
}
