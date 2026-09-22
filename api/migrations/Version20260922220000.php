<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the chosen shipping method and fee on orders.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_method VARCHAR(20) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_label VARCHAR(40) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_fee_cents INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE orders DROP COLUMN shipping_fee_cents');
        $this->connection->executeStatement('ALTER TABLE orders DROP COLUMN shipping_label');
        $this->connection->executeStatement('ALTER TABLE orders DROP COLUMN shipping_method');
    }
}
