<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210310102935 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE transactions ADD order_shipment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C6052497A FOREIGN KEY (order_shipment_id) REFERENCES order_shipments (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EAA81A4C6052497A ON transactions (order_shipment_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C6052497A');
        $this->addSql('DROP INDEX UNIQ_EAA81A4C6052497A ON transactions');
        $this->addSql('ALTER TABLE transactions DROP order_shipment_id');
    }
}
