<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSlug;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique SEO slugs to products.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE products ADD slug VARCHAR(180) DEFAULT '' NOT NULL");
    }

    public function postUp(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, name FROM products ORDER BY created_at ASC, id ASC',
        );
        $taken = [];

        foreach ($rows as $row) {
            $base = ProductSlug::fromName(ProductName::fromString((string) $row['name']));
            $slug = $base;
            $copy = 2;

            while (isset($taken[$slug->value()])) {
                $slug = $base->withCopyNumber($copy);
                ++$copy;
            }

            $taken[$slug->value()] = true;
            $this->connection->update('products', ['slug' => $slug->value()], ['id' => $row['id']]);
        }

        $this->connection->executeStatement('CREATE UNIQUE INDEX uniq_products_slug ON products (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_products_slug');
        $this->addSql('ALTER TABLE products DROP COLUMN slug');
    }
}
