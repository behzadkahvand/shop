<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210525110727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_brand_seller_product_options DROP FOREIGN KEY FK_F85690368DE820D9');
        $this->addSql('DROP INDEX IDX_F85690368DE820D9 ON category_brand_seller_product_options');
        $this->addSql('ALTER TABLE category_brand_seller_product_options DROP seller_id, CHANGE category_id category_id INT NOT NULL, CHANGE brand_id brand_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F856903612469DE244F5D008C964ABE2 ON category_brand_seller_product_options (category_id, brand_id, product_option_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX UNIQ_F856903612469DE244F5D008C964ABE2 ON category_brand_seller_product_options');
        $this->addSql('ALTER TABLE category_brand_seller_product_options ADD seller_id INT DEFAULT NULL, CHANGE category_id category_id INT DEFAULT NULL, CHANGE brand_id brand_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category_brand_seller_product_options ADD CONSTRAINT FK_F85690368DE820D9 FOREIGN KEY (seller_id) REFERENCES sellers (id)');
        $this->addSql('CREATE INDEX IDX_F85690368DE820D9 ON category_brand_seller_product_options (seller_id)');
    }
}
