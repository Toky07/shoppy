<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product categories and assign the demo catalog.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categories (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(180) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_categories_slug ON categories (slug)');
        $this->addSql('ALTER TABLE products ADD COLUMN category_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_products_category_id ON products (category_id)');

        $this->addSql("INSERT INTO categories (id, name, slug) VALUES
            ('11111111-1111-4111-8111-111111111111', 'T-shirts', 't-shirts'),
            ('22222222-2222-4222-8222-222222222222', 'Sweats', 'sweats'),
            ('33333333-3333-4333-8333-333333333333', 'Casquettes', 'casquettes'),
            ('44444444-4444-4444-8444-444444444444', 'Mugs', 'mugs'),
            ('55555555-5555-4555-8555-555555555555', 'Gourdes', 'gourdes'),
            ('66666666-6666-4666-8666-666666666666', 'Sacs', 'sacs'),
            ('77777777-7777-4777-8777-777777777777', 'Carnets', 'carnets'),
            ('88888888-8888-4888-8888-888888888888', 'Posters', 'posters'),
            ('99999999-9999-4999-8999-999999999999', 'Stickers', 'stickers'),
            ('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'Chaussettes', 'chaussettes'),
            ('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 'Coques', 'coques')
        ");

        $this->assign('11111111-1111-4111-8111-111111111111', 'T-shirt%');
        $this->assign('22222222-2222-4222-8222-222222222222', 'Hoodie%');
        $this->assign('22222222-2222-4222-8222-222222222222', 'Sweat%');
        $this->assign('33333333-3333-4333-8333-333333333333', 'Casquette%');
        $this->assign('44444444-4444-4444-8444-444444444444', 'Mug%');
        $this->assign('55555555-5555-4555-8555-555555555555', 'Gourde%');
        $this->assign('66666666-6666-4666-8666-666666666666', 'Tote bag%');
        $this->assign('77777777-7777-4777-8777-777777777777', 'Carnet%');
        $this->assign('88888888-8888-4888-8888-888888888888', 'Poster%');
        $this->assign('99999999-9999-4999-8999-999999999999', 'Pack stickers%');
        $this->assign('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'Chaussettes%');
        $this->assign('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 'Coque%');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_products_category_id');
        $this->addSql('ALTER TABLE products DROP COLUMN category_id');
        $this->addSql('DROP TABLE categories');
    }

    private function assign(string $categoryId, string $namePattern): void
    {
        $this->addSql(
            'UPDATE products SET category_id = ? WHERE name LIKE ? AND category_id IS NULL',
            [$categoryId, $namePattern],
        );
    }
}
