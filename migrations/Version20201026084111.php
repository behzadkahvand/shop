<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201026084111 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_item_log (id INT AUTO_INCREMENT NOT NULL, order_item_id INT NOT NULL, user_id INT DEFAULT NULL, quantity_from INT NOT NULL, quantity_to INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_89D2F146E415FB15 (order_item_id), INDEX IDX_89D2F146A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_item_log ADD CONSTRAINT FK_89D2F146E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_items (id)');
        $this->addSql('ALTER TABLE order_item_log ADD CONSTRAINT FK_89D2F146A76ED395 FOREIGN KEY (user_id) REFERENCES admins (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_item_log');
    }
}
