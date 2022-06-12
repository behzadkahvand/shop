<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210131113320 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE promotion_discount ADD quantity INT DEFAULT 0, ADD unit_amount INT DEFAULT 0');

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE promotion_discount DROP quantity, DROP unit_amount');
    }
}
