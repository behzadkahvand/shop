<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210529092229 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_item_deleted_log (id INT AUTO_INCREMENT NOT NULL, order_item_id INT NOT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_D3DA1E58E415FB15 (order_item_id), INDEX IDX_D3DA1E58A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_item_deleted_log ADD CONSTRAINT FK_D3DA1E58E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_items (id)');
        $this->addSql('ALTER TABLE order_item_deleted_log ADD CONSTRAINT FK_D3DA1E58A76ED395 FOREIGN KEY (user_id) REFERENCES admins (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_item_deleted_log');
    }
}
