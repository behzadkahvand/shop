<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210411052141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_affiliator (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, utm_source VARCHAR(80) NOT NULL, utm_token VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2EBE4FC68D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_affiliator ADD CONSTRAINT FK_2EBE4FC68D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_affiliator');
    }
}
