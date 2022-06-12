<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210110132349 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seller_order_item_status_logs (id INT AUTO_INCREMENT NOT NULL, seller_order_item_id INT DEFAULT NULL, user_id INT NOT NULL, user_type VARCHAR(255) NOT NULL, status_from VARCHAR(255) NOT NULL, status_to VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E655AA01504BEDAC (seller_order_item_id), INDEX IDX_E655AA01A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE seller_order_item_status_logs ADD CONSTRAINT FK_E655AA01504BEDAC FOREIGN KEY (seller_order_item_id) REFERENCES seller_order_items (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE seller_order_item_status_logs');
    }
}
