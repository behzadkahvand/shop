<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201223083351 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promotion_discount ADD order_shipment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE promotion_discount ADD CONSTRAINT FK_27B4104A6052497A FOREIGN KEY (order_shipment_id) REFERENCES order_shipments (id)');
        $this->addSql('CREATE INDEX IDX_27B4104A6052497A ON promotion_discount (order_shipment_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promotion_discount DROP FOREIGN KEY FK_27B4104A6052497A');
        $this->addSql('DROP INDEX IDX_27B4104A6052497A ON promotion_discount');
        $this->addSql('ALTER TABLE promotion_discount DROP order_shipment_id');
    }
}
