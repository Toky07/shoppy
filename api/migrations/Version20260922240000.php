<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922240000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index payment provider references for webhook lookup.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement(
            'CREATE INDEX idx_payments_provider_reference ON payments (provider_reference)',
        );
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('DROP INDEX idx_payments_provider_reference');
    }
}
