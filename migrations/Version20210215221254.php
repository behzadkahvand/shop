<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210215221254 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_shipment_invoice_order_addresses');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD order_address_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAF466D5220 FOREIGN KEY (order_address_id) REFERENCES order_addresses (id)');
        $this->addSql('CREATE INDEX IDX_D81B9AAF466D5220 ON order_shipment_invoices (order_address_id)');

        $this->addSql('ALTER TABLE order_shipment_invoices ADD order_legal_account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAFE5FDE3FB FOREIGN KEY (order_legal_account_id) REFERENCES order_legal_accounts (id)');
        $this->addSql('CREATE INDEX IDX_D81B9AAFE5FDE3FB ON order_shipment_invoices (order_legal_account_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipment_invoices DROP FOREIGN KEY FK_D81B9AAFE5FDE3FB');
        $this->addSql('DROP INDEX IDX_D81B9AAFE5FDE3FB ON order_shipment_invoices');
        $this->addSql('ALTER TABLE order_shipment_invoices DROP order_legal_account_id');

        $this->addSql('CREATE TABLE order_shipment_invoice_order_addresses (id INT AUTO_INCREMENT NOT NULL, order_shipment_invoice_id INT NOT NULL, district_id INT DEFAULT NULL, city_id INT NOT NULL, full_address LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, unit INT DEFAULT NULL, floor VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, number INT NOT NULL, coordinates POINT DEFAULT NULL COMMENT \'(DC2Type:point)\', name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, family VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, national_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, postal_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_CF12F89DB72BDAB3 (order_shipment_invoice_id), INDEX IDX_CF12F89DB08FA272 (district_id), INDEX IDX_CF12F89D8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE order_shipment_invoice_order_addresses ADD CONSTRAINT FK_CF12F89D8BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE order_shipment_invoice_order_addresses ADD CONSTRAINT FK_CF12F89DB08FA272 FOREIGN KEY (district_id) REFERENCES districts (id)');
        $this->addSql('ALTER TABLE order_shipment_invoice_order_addresses ADD CONSTRAINT FK_CF12F89DB72BDAB3 FOREIGN KEY (order_shipment_invoice_id) REFERENCES order_shipment_invoices (id)');
        $this->addSql('ALTER TABLE order_shipment_invoices DROP FOREIGN KEY FK_D81B9AAF466D5220');
        $this->addSql('DROP INDEX IDX_D81B9AAF466D5220 ON order_shipment_invoices');
        $this->addSql('ALTER TABLE order_shipment_invoices DROP order_address_id');
    }
}
