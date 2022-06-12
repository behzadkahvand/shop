<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210314075226 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipment_invoice_items DROP INDEX UNIQ_E8682601504BEDAC, ADD INDEX IDX_E8682601504BEDAC (seller_order_item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipment_invoice_items DROP INDEX IDX_E8682601504BEDAC, ADD UNIQUE INDEX UNIQ_E8682601504BEDAC (seller_order_item_id)');
    }
}
