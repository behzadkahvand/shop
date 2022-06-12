<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201123120438 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_addresses ADD customer_address_id INT DEFAULT NULL AFTER id');
        $this->addSql('ALTER TABLE order_addresses ADD CONSTRAINT FK_D34D0EEE87EABF7 FOREIGN KEY (customer_address_id) REFERENCES customer_addresses (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D34D0EEE87EABF7 ON order_addresses (customer_address_id)');

        $this->addSql('UPDATE `order_addresses` INNER JOIN `orders` ON `orders`.`id` = `order_addresses`.`order_id` SET `order_addresses`.`customer_address_id`= `orders`.`address_id`;');

        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEF5B7AF75');
        $this->addSql('DROP INDEX IDX_E52FFDEEF5B7AF75 ON orders');
        $this->addSql('ALTER TABLE orders DROP address_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders ADD address_id INT DEFAULT NULL AFTER customer_id');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEF5B7AF75 FOREIGN KEY (address_id) REFERENCES customer_addresses (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E52FFDEEF5B7AF75 ON orders (address_id)');

        $this->addSql('UPDATE `orders` INNER JOIN `order_addresses` ON `orders`.`id` = `order_addresses`.`order_id` SET `orders`.`address_id`= `order_addresses`.`customer_address_id`');

        $this->addSql('ALTER TABLE order_addresses DROP FOREIGN KEY FK_D34D0EEE87EABF7');
        $this->addSql('DROP INDEX IDX_D34D0EEE87EABF7 ON order_addresses');
        $this->addSql('ALTER TABLE order_addresses DROP customer_address_id');
    }
}
