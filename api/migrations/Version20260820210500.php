<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820210500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add role to users.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE users ADD role VARCHAR(32) DEFAULT 'customer' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP role');
    }
}
