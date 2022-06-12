<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210201125219 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_better_price_reports (id INT AUTO_INCREMENT NOT NULL, province_id INT DEFAULT NULL, product_id INT NOT NULL, customer_id INT NOT NULL, price BIGINT UNSIGNED NOT NULL, website VARCHAR(255) DEFAULT NULL, store_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6375CD34E946114A (province_id), INDEX IDX_6375CD344584665A (product_id), INDEX IDX_6375CD349395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_6375CD34E946114A FOREIGN KEY (province_id) REFERENCES provinces (id)');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_6375CD344584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_6375CD349395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product_better_price_reports');
    }
}
