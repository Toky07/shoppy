<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track reported and hidden product reviews for admin moderation.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('ALTER TABLE product_reviews ADD hidden BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product_reviews ADD reported BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE product_reviews ALTER hidden DROP DEFAULT');
        $this->addSql('ALTER TABLE product_reviews ALTER reported DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('ALTER TABLE product_reviews DROP hidden');
        $this->addSql('ALTER TABLE product_reviews DROP reported');
    }
}
