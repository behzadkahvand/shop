<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220108090256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added createdBy column to orderShipment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_shipments ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_shipments ADD CONSTRAINT FK_28EEE12CB03A8386 FOREIGN KEY (created_by_id) REFERENCES admins (id)');
        $this->addSql('CREATE INDEX IDX_28EEE12CB03A8386 ON order_shipments (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_shipments DROP FOREIGN KEY FK_28EEE12CB03A8386');
        $this->addSql('DROP INDEX IDX_28EEE12CB03A8386 ON order_shipments');
        $this->addSql('ALTER TABLE order_shipments DROP created_by_id');
    }
}
