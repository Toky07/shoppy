<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a cart version so a checkout can be claimed once.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE carts ADD COLUMN version INTEGER NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE carts DROP COLUMN version');
    }
}
