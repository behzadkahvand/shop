<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207004242 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventory_update_demand (id INT AUTO_INCREMENT NOT NULL, seller_id INT NOT NULL, demand_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, dir_path VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_22563C698DE820D9 (seller_id), INDEX IDX_22563C695D022E59 (demand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory_update_sheet (id INT AUTO_INCREMENT NOT NULL, demand_id INT NOT NULL, fixer_demand_id INT DEFAULT NULL, total_count INT NOT NULL, succeeded_count INT NOT NULL, failed_count INT NOT NULL, status VARCHAR(255) NOT NULL, dir_path VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_ECF75E7D5D022E59 (demand_id), UNIQUE INDEX UNIQ_ECF75E7D90C05A6E (fixer_demand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inventory_update_demand ADD CONSTRAINT FK_22563C698DE820D9 FOREIGN KEY (seller_id) REFERENCES sellers (id)');
        $this->addSql('ALTER TABLE inventory_update_demand ADD CONSTRAINT FK_22563C695D022E59 FOREIGN KEY (demand_id) REFERENCES inventory_update_demand (id)');
        $this->addSql('ALTER TABLE inventory_update_sheet ADD CONSTRAINT FK_ECF75E7D5D022E59 FOREIGN KEY (demand_id) REFERENCES inventory_update_demand (id)');
        $this->addSql('ALTER TABLE inventory_update_sheet ADD CONSTRAINT FK_ECF75E7D90C05A6E FOREIGN KEY (fixer_demand_id) REFERENCES inventory_update_demand (id)');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory_update_demand DROP FOREIGN KEY FK_22563C695D022E59');
        $this->addSql('ALTER TABLE inventory_update_sheet DROP FOREIGN KEY FK_ECF75E7D5D022E59');
        $this->addSql('ALTER TABLE inventory_update_sheet DROP FOREIGN KEY FK_ECF75E7D90C05A6E');
        $this->addSql('DROP TABLE inventory_update_demand');
        $this->addSql('DROP TABLE inventory_update_sheet');
    }
}
