<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210208080836 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_legal_accounts (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, province_id INT NOT NULL, city_id INT NOT NULL, organization_name VARCHAR(255) NOT NULL, economic_code VARCHAR(16) NOT NULL, national_id VARCHAR(60) NOT NULL, registration_id VARCHAR(30) NOT NULL, phone_number VARCHAR(12) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_F968DDA79395C3F3 (customer_id), INDEX IDX_F968DDA7E946114A (province_id), INDEX IDX_F968DDA78BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_legal_accounts ADD CONSTRAINT FK_F968DDA79395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE customer_legal_accounts ADD CONSTRAINT FK_F968DDA7E946114A FOREIGN KEY (province_id) REFERENCES provinces (id)');
        $this->addSql('ALTER TABLE customer_legal_accounts ADD CONSTRAINT FK_F968DDA78BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE customer_legal_accounts');
    }
}
