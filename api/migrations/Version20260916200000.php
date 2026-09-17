<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the media table and drop product-owned images.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE media (id VARCHAR(36) NOT NULL, owner_type VARCHAR(255) NOT NULL, owner_id VARCHAR(36) NOT NULL, filename VARCHAR(255) NOT NULL, relative_path VARCHAR(512) NOT NULL, mime_type VARCHAR(100) NOT NULL, size INTEGER NOT NULL, position INTEGER NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_media_owner ON media (owner_type, owner_id)');
        $this->addSql('DROP TABLE product_images');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_images (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id VARCHAR(36) NOT NULL, url VARCHAR(2048) NOT NULL, position INTEGER NOT NULL, CONSTRAINT FK_PRODUCT_IMAGES_PRODUCT FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_PRODUCT_IMAGES_PRODUCT ON product_images (product_id)');
        $this->addSql('DROP TABLE media');
    }
}
