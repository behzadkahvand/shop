<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220227124814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reference_price, price_top_margin and price_bottom_margin to products table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products ADD reference_price INT DEFAULT NULL, ADD price_top_margin INT DEFAULT NULL, ADD price_bottom_margin INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products DROP reference_price, DROP price_top_margin, DROP price_bottom_margin');
    }
}
