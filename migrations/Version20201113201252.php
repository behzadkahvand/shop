<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201113201252 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_promotion (cart_id VARCHAR(36) NOT NULL, promotion_id INT NOT NULL, INDEX IDX_1BE35A4E1AD5CDBF (cart_id), INDEX IDX_1BE35A4E139DF194 (promotion_id), PRIMARY KEY(cart_id, promotion_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_promotion (order_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_7D9A06298D9F6D38 (order_id), INDEX IDX_7D9A0629139DF194 (promotion_id), PRIMARY KEY(order_id, promotion_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart_promotion ADD CONSTRAINT FK_1BE35A4E1AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_promotion ADD CONSTRAINT FK_1BE35A4E139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_promotion ADD CONSTRAINT FK_7D9A06298D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_promotion ADD CONSTRAINT FK_7D9A0629139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promotion ADD exclusive TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cart_promotion');
        $this->addSql('DROP TABLE order_promotion');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE promotion DROP exclusive');
    }
}
