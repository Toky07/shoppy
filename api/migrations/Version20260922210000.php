<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store shipping and billing addresses on orders.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_recipient VARCHAR(80) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_line1 VARCHAR(120) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_line2 VARCHAR(120) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_postal_code VARCHAR(12) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_city VARCHAR(80) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN shipping_country VARCHAR(2) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN billing_recipient VARCHAR(80) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN billing_line1 VARCHAR(120) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN billing_line2 VARCHAR(120) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN billing_postal_code VARCHAR(12) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN billing_city VARCHAR(80) DEFAULT NULL');
        $this->connection->executeStatement('ALTER TABLE orders ADD COLUMN billing_country VARCHAR(2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        foreach ([
            'billing_country',
            'billing_city',
            'billing_postal_code',
            'billing_line2',
            'billing_line1',
            'billing_recipient',
            'shipping_country',
            'shipping_city',
            'shipping_postal_code',
            'shipping_line2',
            'shipping_line1',
            'shipping_recipient',
        ] as $column) {
            $this->connection->executeStatement('ALTER TABLE orders DROP COLUMN '.$column);
        }
    }
}
