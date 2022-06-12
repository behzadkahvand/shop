<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210112055836 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promotion_discount ADD order_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE promotion_discount ADD CONSTRAINT FK_27B4104AE415FB15 FOREIGN KEY (order_item_id) REFERENCES order_items (id)');
        $this->addSql('CREATE INDEX IDX_27B4104AE415FB15 ON promotion_discount (order_item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promotion_discount DROP FOREIGN KEY FK_27B4104AE415FB15');
        $this->addSql('DROP INDEX IDX_27B4104AE415FB15 ON promotion_discount');
        $this->addSql('ALTER TABLE promotion_discount DROP order_item_id');
    }
}
