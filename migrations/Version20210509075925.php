<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210509075925 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_brand_seller_product_options (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, brand_id INT DEFAULT NULL, seller_id INT DEFAULT NULL, product_option_id INT NOT NULL, INDEX IDX_F856903612469DE2 (category_id), INDEX IDX_F856903644F5D008 (brand_id), INDEX IDX_F85690368DE820D9 (seller_id), INDEX IDX_F8569036C964ABE2 (product_option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_brand_seller_product_option_product_option_value (category_brand_seller_product_option_id INT NOT NULL, product_option_value_id INT NOT NULL, INDEX IDX_5CCBB07BF77D22B3 (category_brand_seller_product_option_id), INDEX IDX_5CCBB07BEBDCCF9B (product_option_value_id), PRIMARY KEY(category_brand_seller_product_option_id, product_option_value_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_brand_seller_product_options ADD CONSTRAINT FK_F856903612469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE category_brand_seller_product_options ADD CONSTRAINT FK_F856903644F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id)');
        $this->addSql('ALTER TABLE category_brand_seller_product_options ADD CONSTRAINT FK_F85690368DE820D9 FOREIGN KEY (seller_id) REFERENCES sellers (id)');
        $this->addSql('ALTER TABLE category_brand_seller_product_options ADD CONSTRAINT FK_F8569036C964ABE2 FOREIGN KEY (product_option_id) REFERENCES product_options (id)');
        $this->addSql('ALTER TABLE category_brand_seller_product_option_product_option_value ADD CONSTRAINT FK_5CCBB07BF77D22B3 FOREIGN KEY (category_brand_seller_product_option_id) REFERENCES category_brand_seller_product_options (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_brand_seller_product_option_product_option_value ADD CONSTRAINT FK_5CCBB07BEBDCCF9B FOREIGN KEY (product_option_value_id) REFERENCES product_option_values (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_brand_seller_product_option_product_option_value DROP FOREIGN KEY FK_5CCBB07BF77D22B3');
        $this->addSql('DROP TABLE category_brand_seller_product_options');
        $this->addSql('DROP TABLE category_brand_seller_product_option_product_option_value');
    }
}
