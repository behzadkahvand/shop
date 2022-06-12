<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210213104531 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_legal_accounts (id INT AUTO_INCREMENT NOT NULL, customer_legal_account_id INT NOT NULL, order_id INT NOT NULL, province_id INT NOT NULL, city_id INT NOT NULL, organization_name VARCHAR(255) NOT NULL, economic_code VARCHAR(16) NOT NULL, national_id VARCHAR(60) NOT NULL, registration_id VARCHAR(30) NOT NULL, phone_number VARCHAR(12) NOT NULL, INDEX IDX_87539DD974AA5FB (customer_legal_account_id), UNIQUE INDEX UNIQ_87539DD8D9F6D38 (order_id), INDEX IDX_87539DDE946114A (province_id), INDEX IDX_87539DD8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_legal_accounts ADD CONSTRAINT FK_87539DD974AA5FB FOREIGN KEY (customer_legal_account_id) REFERENCES customer_legal_accounts (id)');
        $this->addSql('ALTER TABLE order_legal_accounts ADD CONSTRAINT FK_87539DD8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('ALTER TABLE order_legal_accounts ADD CONSTRAINT FK_87539DDE946114A FOREIGN KEY (province_id) REFERENCES provinces (id)');
        $this->addSql('ALTER TABLE order_legal_accounts ADD CONSTRAINT FK_87539DD8BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE orders ADD is_legal TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_legal_accounts');
        $this->addSql('ALTER TABLE orders DROP is_legal');
    }
}
