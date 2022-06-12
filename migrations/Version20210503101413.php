<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210503101413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE product_notify_requests (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, customer_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5E9B550E4584665A (product_id), INDEX IDX_5E9B550E9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE product_notify_requests ADD CONSTRAINT FK_5E9B550E4584665A FOREIGN KEY (product_id) REFERENCES products (id)'
        );
        $this->addSql(
            'ALTER TABLE product_notify_requests ADD CONSTRAINT FK_5E9B550E9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product_notify_requests');
    }
}
