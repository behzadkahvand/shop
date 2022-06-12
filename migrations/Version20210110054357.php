<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210110054357 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_cancel_reason_orders (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, cancel_reason_id INT NOT NULL, UNIQUE INDEX UNIQ_47B1E3E38D9F6D38 (order_id), INDEX IDX_47B1E3E3EE1A430C (cancel_reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_cancel_reasons (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(64) NOT NULL, reason VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1C37F74977153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_cancel_reason_orders ADD CONSTRAINT FK_47B1E3E38D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE order_cancel_reason_orders ADD CONSTRAINT FK_47B1E3E3EE1A430C FOREIGN KEY (cancel_reason_id) REFERENCES order_cancel_reasons (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_cancel_reason_orders DROP FOREIGN KEY FK_47B1E3E3EE1A430C');
        $this->addSql('DROP TABLE order_cancel_reason_orders');
        $this->addSql('DROP TABLE order_cancel_reasons');
    }
}
