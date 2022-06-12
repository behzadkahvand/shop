<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201113214049 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carts ADD promotion_coupon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE carts ADD CONSTRAINT FK_4E004AAC17B24436 FOREIGN KEY (promotion_coupon_id) REFERENCES promotion_coupon (id)');
        $this->addSql('CREATE INDEX IDX_4E004AAC17B24436 ON carts (promotion_coupon_id)');
        $this->addSql('ALTER TABLE orders ADD promotion_coupon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE17B24436 FOREIGN KEY (promotion_coupon_id) REFERENCES promotion_coupon (id)');
        $this->addSql('CREATE INDEX IDX_E52FFDEE17B24436 ON orders (promotion_coupon_id)');
        $this->addSql('ALTER TABLE promotion ADD used INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE promotion_coupon ADD used INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carts DROP FOREIGN KEY FK_4E004AAC17B24436');
        $this->addSql('DROP INDEX IDX_4E004AAC17B24436 ON carts');
        $this->addSql('ALTER TABLE carts DROP promotion_coupon_id');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE17B24436');
        $this->addSql('DROP INDEX IDX_E52FFDEE17B24436 ON orders');
        $this->addSql('ALTER TABLE orders DROP promotion_coupon_id');
        $this->addSql('ALTER TABLE promotion DROP used');
        $this->addSql('ALTER TABLE promotion_coupon DROP used');
    }
}
