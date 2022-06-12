<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201109194451 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promotion (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, priority INT NOT NULL, usage_limit INT DEFAULT NULL, coupon_based TINYINT(1) NOT NULL, starts_at DATETIME DEFAULT NULL, ends_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promotion_action (id INT AUTO_INCREMENT NOT NULL, promotion_id INT NOT NULL, type VARCHAR(255) NOT NULL, configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_5276A7AF139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promotion_coupon (id INT AUTO_INCREMENT NOT NULL, promotion_id INT NOT NULL, code VARCHAR(255) NOT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7105143F77153098 (code), INDEX IDX_7105143F139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promotion_rule (id INT AUTO_INCREMENT NOT NULL, promotion_id INT NOT NULL, type VARCHAR(255) NOT NULL, configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_F0222453139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promotion_action ADD CONSTRAINT FK_5276A7AF139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
        $this->addSql('ALTER TABLE promotion_coupon ADD CONSTRAINT FK_7105143F139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
        $this->addSql('ALTER TABLE promotion_rule ADD CONSTRAINT FK_F0222453139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE promotion_action DROP FOREIGN KEY FK_5276A7AF139DF194');
        $this->addSql('ALTER TABLE promotion_coupon DROP FOREIGN KEY FK_7105143F139DF194');
        $this->addSql('ALTER TABLE promotion_rule DROP FOREIGN KEY FK_F0222453139DF194');
        $this->addSql('DROP TABLE promotion');
        $this->addSql('DROP TABLE promotion_action');
        $this->addSql('DROP TABLE promotion_coupon');
        $this->addSql('DROP TABLE promotion_rule');
    }
}
