<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210428060041 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_product_identifiers (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, required TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_91621DE812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_identifiers (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, identifier VARCHAR(32) NOT NULL, INDEX IDX_1A31F7D14584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_product_identifiers ADD CONSTRAINT FK_91621DE812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE product_identifiers ADD CONSTRAINT FK_1A31F7D14584665A FOREIGN KEY (product_id) REFERENCES products (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE category_product_identifiers');
        $this->addSql('DROP TABLE product_identifiers');
    }

    /**
     * @inheritDoc
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
