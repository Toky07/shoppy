<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align invoice foreign-key index names with Doctrine mapping.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('ALTER INDEX idx_invoice_items_invoice_id RENAME TO IDX_DCC4B9F82989F1FD');
        $this->addSql('ALTER INDEX idx_invoice_order_snapshot_items_order_id RENAME TO IDX_285D033F8D9F6D38');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'This migration targets PostgreSQL.',
        );

        $this->addSql('ALTER INDEX IDX_DCC4B9F82989F1FD RENAME TO idx_invoice_items_invoice_id');
        $this->addSql('ALTER INDEX IDX_285D033F8D9F6D38 RENAME TO idx_invoice_order_snapshot_items_order_id');
    }
}
