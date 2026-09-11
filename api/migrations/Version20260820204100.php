<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820204100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the credentials table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE credentials (user_id VARCHAR(36) NOT NULL, password_hash CLOB NOT NULL, PRIMARY KEY (user_id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE credentials');
    }
}
