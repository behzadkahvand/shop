<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210310064600 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE transaction_metas (id INT AUTO_INCREMENT NOT NULL, transaction_id INT NOT NULL, `key` VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_EC59297C2FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction_metas ADD CONSTRAINT FK_EC59297C2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transactions (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE transaction_metas');
    }
}
