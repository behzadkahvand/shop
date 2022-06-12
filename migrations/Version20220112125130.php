<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112125130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix migration diff.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('set foreign_key_checks=0;');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories DROP FOREIGN KEY FK_DEC1580D12469DE2');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories DROP FOREIGN KEY FK_DEC1580D9AF24C5F');
        $this->addSql('DROP INDEX idx_dec1580d9af24c5f ON marketing_seller_landings_categories');
        $this->addSql('CREATE INDEX IDX_A65DCE4C9AF24C5F ON marketing_seller_landings_categories (marketing_seller_landing_id)');
        $this->addSql('DROP INDEX idx_dec1580d12469de2 ON marketing_seller_landings_categories');
        $this->addSql('CREATE INDEX IDX_A65DCE4C12469DE2 ON marketing_seller_landings_categories (category_id)');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories ADD CONSTRAINT FK_DEC1580D12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories ADD CONSTRAINT FK_DEC1580D9AF24C5F FOREIGN KEY (marketing_seller_landing_id) REFERENCES marketing_seller_landings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_better_price_reports DROP FOREIGN KEY FK_6375CD344584665A');
        $this->addSql('ALTER TABLE product_better_price_reports DROP FOREIGN KEY FK_6375CD349395C3F3');
        $this->addSql('ALTER TABLE product_better_price_reports DROP FOREIGN KEY FK_6375CD34E946114A');
        $this->addSql('DROP INDEX idx_6375cd34e946114a ON product_better_price_reports');
        $this->addSql('CREATE INDEX IDX_7D2D7EB7E946114A ON product_better_price_reports (province_id)');
        $this->addSql('DROP INDEX idx_6375cd344584665a ON product_better_price_reports');
        $this->addSql('CREATE INDEX IDX_7D2D7EB74584665A ON product_better_price_reports (product_id)');
        $this->addSql('DROP INDEX idx_6375cd349395c3f3 ON product_better_price_reports');
        $this->addSql('CREATE INDEX IDX_7D2D7EB79395C3F3 ON product_better_price_reports (customer_id)');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_6375CD344584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_6375CD349395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_6375CD34E946114A FOREIGN KEY (province_id) REFERENCES provinces (id)');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_AA18006F4584665A');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_AA18006F8D9F6D38');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_AA18006F9395C3F3');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_AA18006F9EEA759');
        $this->addSql('DROP INDEX idx_aa18006f9395c3f3 ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_C6A9ABC29395C3F3 ON rate_and_reviews (customer_id)');
        $this->addSql('DROP INDEX idx_aa18006f4584665a ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_C6A9ABC24584665A ON rate_and_reviews (product_id)');
        $this->addSql('DROP INDEX idx_aa18006f9eea759 ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_C6A9ABC29EEA759 ON rate_and_reviews (inventory_id)');
        $this->addSql('DROP INDEX idx_aa18006f8d9f6d38 ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_C6A9ABC28D9F6D38 ON rate_and_reviews (order_id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id)');
        $this->addSql('DROP INDEX IDX_936C863D1B5771DD4B365660 ON inventories');
        $this->addSql('DROP INDEX IDX_936C863DCBD07149 ON inventories');
        $this->addSql('DROP INDEX IDX_4AB29773BB8880C ON inventories');
        $this->addSql('DROP INDEX IDX_936C863D72B4E3F7 ON inventories');
        $this->addSql('ALTER TABLE products CHANGE colors colors LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE promotion_discount CHANGE cart_id cart_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('set foreign_key_checks=1;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('set foreign_key_checks=0;');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories ADD CONSTRAINT FK_A65DCE4C9AF24C5F FOREIGN KEY (marketing_seller_landing_id) REFERENCES marketing_seller_landings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories ADD CONSTRAINT FK_A65DCE4C12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_a65dce4c9af24c5f ON marketing_seller_landings_categories');
        $this->addSql('CREATE INDEX IDX_DEC1580D9AF24C5F ON marketing_seller_landings_categories (marketing_seller_landing_id)');
        $this->addSql('DROP INDEX idx_a65dce4c12469de2 ON marketing_seller_landings_categories');
        $this->addSql('CREATE INDEX IDX_DEC1580D12469DE2 ON marketing_seller_landings_categories (category_id)');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories DROP FOREIGN KEY FK_A65DCE4C9AF24C5F');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories DROP FOREIGN KEY FK_A65DCE4C12469DE2');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_7D2D7EB7E946114A FOREIGN KEY (province_id) REFERENCES provinces (id)');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_7D2D7EB74584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_better_price_reports ADD CONSTRAINT FK_7D2D7EB79395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('DROP INDEX idx_7d2d7eb7e946114a ON product_better_price_reports');
        $this->addSql('CREATE INDEX IDX_6375CD34E946114A ON product_better_price_reports (province_id)');
        $this->addSql('DROP INDEX idx_7d2d7eb74584665a ON product_better_price_reports');
        $this->addSql('CREATE INDEX IDX_6375CD344584665A ON product_better_price_reports (product_id)');
        $this->addSql('DROP INDEX idx_7d2d7eb79395c3f3 ON product_better_price_reports');
        $this->addSql('CREATE INDEX IDX_6375CD349395C3F3 ON product_better_price_reports (customer_id)');
        $this->addSql('ALTER TABLE product_better_price_reports DROP FOREIGN KEY FK_7D2D7EB7E946114A');
        $this->addSql('ALTER TABLE product_better_price_reports DROP FOREIGN KEY FK_7D2D7EB74584665A');
        $this->addSql('ALTER TABLE product_better_price_reports DROP FOREIGN KEY FK_7D2D7EB79395C3F3');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_C6A9ABC29395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_C6A9ABC24584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_C6A9ABC29EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_C6A9ABC28D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('DROP INDEX idx_c6a9abc29eea759 ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_AA18006F9EEA759 ON rate_and_reviews (inventory_id)');
        $this->addSql('DROP INDEX idx_c6a9abc28d9f6d38 ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_AA18006F8D9F6D38 ON rate_and_reviews (order_id)');
        $this->addSql('DROP INDEX idx_c6a9abc29395c3f3 ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_AA18006F9395C3F3 ON rate_and_reviews (customer_id)');
        $this->addSql('DROP INDEX idx_c6a9abc24584665a ON rate_and_reviews');
        $this->addSql('CREATE INDEX IDX_AA18006F4584665A ON rate_and_reviews (product_id)');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_C6A9ABC29395C3F3');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_C6A9ABC24584665A');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_C6A9ABC29EEA759');
        $this->addSql('ALTER TABLE rate_and_reviews DROP FOREIGN KEY FK_C6A9ABC28D9F6D38');
        $this->addSql('CREATE INDEX IDX_936C863D1B5771DD4B365660 ON inventories (is_active, seller_stock)');
        $this->addSql('CREATE INDEX IDX_936C863DCBD07149 ON inventories (final_price)');
        $this->addSql('CREATE INDEX IDX_4AB29773BB8880C ON inventories (has_campaign)');
        $this->addSql('CREATE INDEX IDX_936C863D72B4E3F7 ON inventories (lead_time)');
        $this->addSql('ALTER TABLE products CHANGE colors colors LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE promotion_discount CHANGE cart_id cart_id VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('set foreign_key_checks=1;');
    }
}
