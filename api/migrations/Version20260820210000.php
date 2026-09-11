<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the access_tokens table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE access_tokens (token_hash VARCHAR(64) NOT NULL, user_id VARCHAR(36) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (token_hash))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE access_tokens');
    }
}
