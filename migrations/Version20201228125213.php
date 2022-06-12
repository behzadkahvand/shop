<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201228125213 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seller_package_items DROP quantity');
    }

    public function down(Schema $schema) : void
    {
        $updateQuery  = 'UPDATE seller_package_items spi ';
        $updateQuery .= 'INNER JOIN seller_order_items soi ON soi.package_item_id = spi.id ';
        $updateQuery .= 'INNER JOIN order_items oi ON  oi.id = soi.order_item_id ';
        $updateQuery .= 'SET spi.quantity = oi.quantity';

        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seller_package_items ADD quantity INT NOT NULL AFTER package_id');
        $this->addSql($updateQuery);
    }
}
