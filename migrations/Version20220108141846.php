<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220108141846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change stock and supplies_in to seller_stock and lead_time in inventories and order_items table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `inventories` CHANGE `stock` `seller_stock` INT(10) UNSIGNED NOT NULL;');
        $this->addSql('ALTER TABLE `inventories` CHANGE `supplies_in` `lead_time` INT(10) UNSIGNED NOT NULL;');
        $this->addSql('ALTER TABLE `order_items` CHANGE `supplies_in` `lead_time` INT(11) NULL DEFAULT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `inventories` CHANGE `seller_stock` `stock` INT(10) UNSIGNED NOT NULL;');
        $this->addSql('ALTER TABLE `inventories` CHANGE `lead_time` `supplies_in` INT(10) UNSIGNED NOT NULL;');
        $this->addSql('ALTER TABLE `order_items` CHANGE `lead_time` `supplies_in` INT(11) NULL DEFAULT NULL;');
    }
}
