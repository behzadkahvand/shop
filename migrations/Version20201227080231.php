<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227080231 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE sellers ADD identifier VARCHAR(10) DEFAULT NULL AFTER id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AFFE6BEF772E836A ON sellers (identifier)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX UNIQ_AFFE6BEF772E836A ON sellers');
        $this->addSql('ALTER TABLE sellers DROP identifier');
    }
}
