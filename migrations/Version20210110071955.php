<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210110071955 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE rate_and_reviews (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, product_id INT NOT NULL, inventory_id INT DEFAULT NULL, order_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT NOT NULL, suggestion VARCHAR(255) NOT NULL, rate SMALLINT UNSIGNED NOT NULL, anonymous TINYINT(1) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_AA18006F9395C3F3 (customer_id), INDEX IDX_AA18006F4584665A (product_id), INDEX IDX_AA18006F9EEA759 (inventory_id), INDEX IDX_AA18006F8D9F6D38 (order_id), UNIQUE INDEX customer_product (customer_id, product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id)');
        $this->addSql('ALTER TABLE rate_and_reviews ADD CONSTRAINT FK_AA18006F8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE rate_and_reviews');
    }
}
