<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201121123047 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_27B4104A23EDC87 ON promotion_discount');
        $this->addSql('ALTER TABLE promotion_discount ADD order_id INT DEFAULT NULL, ADD cart_id VARCHAR(64) DEFAULT NULL, DROP subject_id');
        $this->addSql('ALTER TABLE promotion_discount ADD CONSTRAINT FK_27B4104A8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE promotion_discount ADD CONSTRAINT FK_27B4104A1AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id)');
        $this->addSql('CREATE INDEX IDX_27B4104A8D9F6D38 ON promotion_discount (order_id)');
        $this->addSql('CREATE INDEX IDX_27B4104A1AD5CDBF ON promotion_discount (cart_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promotion_discount DROP FOREIGN KEY FK_27B4104A8D9F6D38');
        $this->addSql('ALTER TABLE promotion_discount DROP FOREIGN KEY FK_27B4104A1AD5CDBF');
        $this->addSql('DROP INDEX IDX_27B4104A8D9F6D38 ON promotion_discount');
        $this->addSql('DROP INDEX IDX_27B4104A1AD5CDBF ON promotion_discount');
        $this->addSql('ALTER TABLE promotion_discount ADD subject_id INT NOT NULL, DROP order_id, DROP cart_id');
        $this->addSql('CREATE INDEX IDX_27B4104A23EDC87 ON promotion_discount (subject_id)');
    }
}
