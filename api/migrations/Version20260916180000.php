<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store a gallery of product images.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_images (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id VARCHAR(36) NOT NULL, url VARCHAR(2048) NOT NULL, position INTEGER NOT NULL, CONSTRAINT FK_PRODUCT_IMAGES_PRODUCT FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_PRODUCT_IMAGES_PRODUCT ON product_images (product_id)');
        $this->addSql('INSERT INTO product_images (product_id, url, position) SELECT id, image_url, 0 FROM products WHERE image_url IS NOT NULL AND image_url != \'\'');
        $this->addSql('ALTER TABLE products DROP COLUMN image_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD COLUMN image_url VARCHAR(2048) DEFAULT NULL');
        $this->addSql('UPDATE products SET image_url = (SELECT url FROM product_images WHERE product_images.product_id = products.id ORDER BY position ASC LIMIT 1)');
        $this->addSql('DROP TABLE product_images');
    }
}
