<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102070455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_shipment_invoice_discounts DROP FOREIGN KEY FK_B7DF529445788400');
        $this->addSql('ALTER TABLE order_shipment_invoice_customers DROP FOREIGN KEY FK_29DB1E0DB72BDAB3');
        $this->addSql('ALTER TABLE order_shipment_invoice_items DROP FOREIGN KEY FK_E8682601B72BDAB3');
        $this->addSql('DROP TABLE order_shipment_invoice_customers');
        $this->addSql('DROP TABLE order_shipment_invoice_discounts');
        $this->addSql('DROP TABLE order_shipment_invoice_items');
        $this->addSql('DROP TABLE order_shipment_invoices');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_shipment_invoice_customers (id INT AUTO_INCREMENT NOT NULL, order_shipment_invoice_id INT NOT NULL, national_number VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, mobile VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, birthday DATE DEFAULT NULL, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, family VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_29DB1E0DB72BDAB3 (order_shipment_invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE order_shipment_invoice_discounts (id INT AUTO_INCREMENT NOT NULL, order_shipment_invoice_item_id INT NOT NULL, action_id INT NOT NULL, unit_amount BIGINT DEFAULT NULL, quantity INT DEFAULT NULL, amount BIGINT NOT NULL, INDEX IDX_B7DF529445788400 (order_shipment_invoice_item_id), INDEX IDX_B7DF52949D32F035 (action_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE order_shipment_invoice_items (id INT AUTO_INCREMENT NOT NULL, order_shipment_invoice_id INT NOT NULL, inventory_id INT NOT NULL, seller_order_item_id INT DEFAULT NULL, product_id INT NOT NULL, guaranty_id INT DEFAULT NULL, color_id INT DEFAULT NULL, other_option_id INT DEFAULT NULL, subtotal BIGINT UNSIGNED NOT NULL, grand_total BIGINT UNSIGNED NOT NULL, quantity INT NOT NULL, price BIGINT UNSIGNED NOT NULL, commission DOUBLE PRECISION DEFAULT NULL, product_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_E86826013B3FE3C1 (guaranty_id), INDEX IDX_E86826014584665A (product_id), INDEX IDX_E8682601504BEDAC (seller_order_item_id), INDEX IDX_E8682601793B94DA (other_option_id), INDEX IDX_E86826017ADA1FB5 (color_id), INDEX IDX_E86826019EEA759 (inventory_id), INDEX IDX_E8682601B72BDAB3 (order_shipment_invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE order_shipment_invoices (id INT AUTO_INCREMENT NOT NULL, order_shipment_id INT NOT NULL, updated_by_id INT DEFAULT NULL, method_id INT NOT NULL, transaction_id INT DEFAULT NULL, period_id INT DEFAULT NULL, shipping_category_id INT NOT NULL, order_address_id INT NOT NULL, order_legal_account_id INT DEFAULT NULL, created_at DATETIME NOT NULL, sub_total BIGINT NOT NULL, grand_total BIGINT UNSIGNED NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, signature VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, tracking_code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, category_delivery_range JSON CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, delivery_date DATE NOT NULL, description VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, pod_code INT UNSIGNED DEFAULT NULL, INDEX IDX_D81B9AAF19883967 (method_id), INDEX IDX_D81B9AAF466D5220 (order_address_id), INDEX IDX_D81B9AAF6052497A (order_shipment_id), INDEX IDX_D81B9AAF896DBBDE (updated_by_id), INDEX IDX_D81B9AAF9E2D1A41 (shipping_category_id), INDEX IDX_D81B9AAFE5FDE3FB (order_legal_account_id), INDEX IDX_D81B9AAFEC8B7ADE (period_id), UNIQUE INDEX UNIQ_D81B9AAF2FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE order_shipment_invoice_customers ADD CONSTRAINT FK_29DB1E0DB72BDAB3 FOREIGN KEY (order_shipment_invoice_id) REFERENCES order_shipment_invoices (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_discounts ADD CONSTRAINT FK_B7DF529445788400 FOREIGN KEY (order_shipment_invoice_item_id) REFERENCES order_shipment_invoice_items (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_discounts ADD CONSTRAINT FK_B7DF52949D32F035 FOREIGN KEY (action_id) REFERENCES promotion_action (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E86826013B3FE3C1 FOREIGN KEY (guaranty_id) REFERENCES product_option_values (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E86826014584665A FOREIGN KEY (product_id) REFERENCES products (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E8682601504BEDAC FOREIGN KEY (seller_order_item_id) REFERENCES seller_order_items (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E8682601793B94DA FOREIGN KEY (other_option_id) REFERENCES product_option_values (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E86826017ADA1FB5 FOREIGN KEY (color_id) REFERENCES product_option_values (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E86826019EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoice_items ADD CONSTRAINT FK_E8682601B72BDAB3 FOREIGN KEY (order_shipment_invoice_id) REFERENCES order_shipment_invoices (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAF19883967 FOREIGN KEY (method_id) REFERENCES shipping_methods (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAF2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transactions (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAF466D5220 FOREIGN KEY (order_address_id) REFERENCES order_addresses (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAF6052497A FOREIGN KEY (order_shipment_id) REFERENCES order_shipments (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAF896DBBDE FOREIGN KEY (updated_by_id) REFERENCES admins (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAF9E2D1A41 FOREIGN KEY (shipping_category_id) REFERENCES shipping_categories (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAFE5FDE3FB FOREIGN KEY (order_legal_account_id) REFERENCES order_legal_accounts (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_shipment_invoices ADD CONSTRAINT FK_D81B9AAFEC8B7ADE FOREIGN KEY (period_id) REFERENCES shipping_periods (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
