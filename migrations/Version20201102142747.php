<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102142747 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_deliveries DROP FOREIGN KEY FK_4919D51612469DE2');
        $this->addSql('DROP INDEX UNIQ_4919D51612469DE2 ON category_deliveries');
        $this->addSql('ALTER TABLE category_deliveries DROP category_id');
        $this->addSql('ALTER TABLE shipping_categories ADD delivery_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shipping_categories ADD CONSTRAINT FK_8208163612136921 FOREIGN KEY (delivery_id) REFERENCES category_deliveries (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8208163612136921 ON shipping_categories (delivery_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_deliveries ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE category_deliveries ADD CONSTRAINT FK_4919D51612469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4919D51612469DE2 ON category_deliveries (category_id)');
        $this->addSql('ALTER TABLE shipping_categories DROP FOREIGN KEY FK_8208163612136921');
        $this->addSql('DROP INDEX UNIQ_8208163612136921 ON shipping_categories');
        $this->addSql('ALTER TABLE shipping_categories DROP delivery_id');
    }
}
