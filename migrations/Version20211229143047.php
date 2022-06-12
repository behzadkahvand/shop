<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211229143047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_active to admins, sellers and customers table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admins ADD is_active TINYINT(1) DEFAULT \'1\' NOT NULL, DROP active');
        $this->addSql('ALTER TABLE customers ADD is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE sellers ADD is_active TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admins ADD active TINYINT(1) NOT NULL, DROP is_active');
        $this->addSql('ALTER TABLE customers DROP is_active');
        $this->addSql('ALTER TABLE sellers DROP is_active');
    }
}
