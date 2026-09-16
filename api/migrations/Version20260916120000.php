<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store payment provider and checkout reference.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payments ADD COLUMN provider VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE payments ADD COLUMN provider_reference VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payments DROP COLUMN provider');
        $this->addSql('ALTER TABLE payments DROP COLUMN provider_reference');
    }
}
