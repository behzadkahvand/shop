<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211116085527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables: return_requests, return_request_items, return_reasons, return_verification_reasons';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE return_reasons (id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE return_request_items (id INT AUTO_INCREMENT NOT NULL, request_id INT DEFAULT NULL, order_item_id INT DEFAULT NULL, return_reason_id INT DEFAULT NULL, quantity INT NOT NULL, description VARCHAR(512) DEFAULT NULL, status VARCHAR(255) NOT NULL, is_returnable TINYINT(1) NOT NULL, refund_amount INT NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, updated_by VARCHAR(255) DEFAULT NULL, INDEX IDX_386C6763427EB8A5 (request_id), INDEX IDX_386C6763E415FB15 (order_item_id), INDEX IDX_386C6763ACA2AB22 (return_reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE return_requests (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, created_at DATETIME NOT NULL, return_date DATETIME NOT NULL, INDEX IDX_6DFF31068D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE return_verification_reasons (id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE return_request_items ADD CONSTRAINT FK_386C6763427EB8A5 FOREIGN KEY (request_id) REFERENCES return_requests (id)');
        $this->addSql('ALTER TABLE return_request_items ADD CONSTRAINT FK_386C6763E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_items (id)');
        $this->addSql('ALTER TABLE return_request_items ADD CONSTRAINT FK_386C6763ACA2AB22 FOREIGN KEY (return_reason_id) REFERENCES return_reasons (id)');
        $this->addSql('ALTER TABLE return_requests ADD CONSTRAINT FK_6DFF31068D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE return_request_items DROP FOREIGN KEY FK_386C6763ACA2AB22');
        $this->addSql('ALTER TABLE return_request_items DROP FOREIGN KEY FK_386C6763427EB8A5');
        $this->addSql('DROP TABLE return_reasons');
        $this->addSql('DROP TABLE return_request_items');
        $this->addSql('DROP TABLE return_requests');
        $this->addSql('DROP TABLE return_verification_reasons');
    }
}
