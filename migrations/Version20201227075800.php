<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227075800 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('DROP INDEX UNIQ_AFFE6BEF77153098 ON sellers');
        $this->addSql('DROP INDEX UNIQ_AFFE6BEF772E836A ON sellers');
        $this->addSql('ALTER TABLE sellers DROP code, DROP identifier');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE sellers ADD code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD identifier INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AFFE6BEF77153098 ON sellers (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AFFE6BEF772E836A ON sellers (identifier)');
    }
}
