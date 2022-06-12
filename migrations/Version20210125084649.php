<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210125084649 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shipping_categories ADD title VARCHAR(255) DEFAULT NULL AFTER name');
        $this->addSql('UPDATE `shipping_categories` SET `title` = \'عادی\' WHERE `shipping_categories`.`id` = 1');
        $this->addSql('UPDATE `shipping_categories` SET `title` = \'سنگین\' WHERE `shipping_categories`.`id` = 2');
        $this->addSql('UPDATE `shipping_categories` SET `title` = \'فوق سنگین\' WHERE `shipping_categories`.`id` = 3');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shipping_categories DROP title');
    }
}
