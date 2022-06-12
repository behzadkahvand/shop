<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210524075337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE initial_seller_scores (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE normal_seller_scores (id INT NOT NULL, seller_order_item_id INT NOT NULL, UNIQUE INDEX UNIQ_E0F41E5E504BEDAC (seller_order_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seller_score_factors (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(255) NOT NULL, score DOUBLE PRECISION NOT NULL, type VARCHAR(16) NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_CE35C4CA772E836A (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seller_score_update (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, dir_path VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, errors LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seller_scores (id INT AUTO_INCREMENT NOT NULL, seller_id INT NOT NULL, seller_score_factor_id INT NOT NULL, score DOUBLE PRECISION NOT NULL, type VARCHAR(16) NOT NULL, final_score DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, score_type VARCHAR(255) NOT NULL, INDEX IDX_B220F0748DE820D9 (seller_id), INDEX IDX_B220F07423722F8C (seller_score_factor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE initial_seller_scores ADD CONSTRAINT FK_978C32C0BF396750 FOREIGN KEY (id) REFERENCES seller_scores (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE normal_seller_scores ADD CONSTRAINT FK_E0F41E5E504BEDAC FOREIGN KEY (seller_order_item_id) REFERENCES seller_order_items (id)');
        $this->addSql('ALTER TABLE normal_seller_scores ADD CONSTRAINT FK_E0F41E5EBF396750 FOREIGN KEY (id) REFERENCES seller_scores (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seller_scores ADD CONSTRAINT FK_B220F0748DE820D9 FOREIGN KEY (seller_id) REFERENCES sellers (id)');
        $this->addSql('ALTER TABLE seller_scores ADD CONSTRAINT FK_B220F07423722F8C FOREIGN KEY (seller_score_factor_id) REFERENCES seller_score_factors (id)');

        $this->addSql('ALTER TABLE sellers ADD score LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seller_scores DROP FOREIGN KEY FK_B220F07423722F8C');
        $this->addSql('ALTER TABLE initial_seller_scores DROP FOREIGN KEY FK_978C32C0BF396750');
        $this->addSql('ALTER TABLE normal_seller_scores DROP FOREIGN KEY FK_E0F41E5EBF396750');
        $this->addSql('DROP TABLE initial_seller_scores');
        $this->addSql('DROP TABLE normal_seller_scores');
        $this->addSql('DROP TABLE seller_score_factors');
        $this->addSql('DROP TABLE seller_score_update');
        $this->addSql('DROP TABLE seller_scores');

        $this->addSql('ALTER TABLE sellers DROP score');
    }
}
