<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201122123747 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipments DROP INDEX IDX_28EEE12C2FC0CB0F, ADD UNIQUE INDEX UNIQ_28EEE12C2FC0CB0F (transaction_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipments DROP INDEX UNIQ_28EEE12C2FC0CB0F, ADD INDEX IDX_28EEE12C2FC0CB0F (transaction_id)');
    }
}
