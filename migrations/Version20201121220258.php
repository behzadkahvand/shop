<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201121220258 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promotion_coupon_customer (promotion_coupon_id INT NOT NULL, customer_id INT NOT NULL, INDEX IDX_34C60A7817B24436 (promotion_coupon_id), INDEX IDX_34C60A789395C3F3 (customer_id), PRIMARY KEY(promotion_coupon_id, customer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promotion_coupon_customer ADD CONSTRAINT FK_34C60A7817B24436 FOREIGN KEY (promotion_coupon_id) REFERENCES promotion_coupon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promotion_coupon_customer ADD CONSTRAINT FK_34C60A789395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE promotion_coupon_customer');
    }
}
