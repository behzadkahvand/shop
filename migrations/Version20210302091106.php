<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210302091106 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipment_invoice_items DROP FOREIGN KEY FK_E8682601DB4B0220');
        $this->addSql('ALTER TABLE order_shipment_invoice_items DROP FOREIGN KEY FK_E8682601498DA827');
        $this->addSql('DROP INDEX IDX_E8682601DB4B0220 ON order_shipment_invoice_items');
        $this->addSql('DROP INDEX IDX_E8682601498DA827 ON order_shipment_invoice_items');
        $this->addSql('ALTER TABLE order_shipment_invoice_items CHANGE guarantee_id guaranty_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_shipment_invoice_items CHANGE size_id other_option_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E86826013B3FE3C1 FOREIGN KEY (guaranty_id) REFERENCES product_option_values (id)');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E8682601793B94DA FOREIGN KEY (other_option_id) REFERENCES product_option_values (id)');
        $this->addSql('CREATE INDEX IDX_E86826013B3FE3C1 ON order_shipment_invoice_items (guaranty_id)');
        $this->addSql('CREATE INDEX IDX_E8682601793B94DA ON order_shipment_invoice_items (other_option_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipment_invoice_items DROP FOREIGN KEY FK_E86826013B3FE3C1');
        $this->addSql('ALTER TABLE order_shipment_invoice_items DROP FOREIGN KEY FK_E8682601793B94DA');
        $this->addSql('DROP INDEX IDX_E86826013B3FE3C1 ON order_shipment_invoice_items');
        $this->addSql('DROP INDEX IDX_E8682601793B94DA ON order_shipment_invoice_items');
        $this->addSql('ALTER TABLE order_shipment_invoice_items CHANGE other_option_id size_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_shipment_invoice_items CHANGE guaranty_id guarantee_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E8682601DB4B0220 FOREIGN KEY (guarantee_id) REFERENCES product_option_values (id)');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E8682601498DA827 FOREIGN KEY (size_id) REFERENCES product_option_values (id)');
        $this->addSql('CREATE INDEX IDX_E8682601DB4B0220 ON order_shipment_invoice_items (guarantee_id)');
        $this->addSql('CREATE INDEX IDX_E8682601498DA827 ON order_shipment_invoice_items (size_id)');
    }
}
