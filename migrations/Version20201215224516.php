<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201215224516 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coupon_generator_instruction (id INT AUTO_INCREMENT NOT NULL, promotion_id INT NOT NULL, amount INT NOT NULL, prefix VARCHAR(127) NOT NULL, code_length INT NOT NULL, suffix VARCHAR(127) NOT NULL, expires_at DATETIME DEFAULT NULL, status VARCHAR(10) NOT NULL, INDEX IDX_C1E19C95139DF194 (promotion_id), INDEX prefix__code_length__suffix_status (prefix, code_length, suffix, status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coupon_generator_instruction ADD CONSTRAINT FK_C1E19C95139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE coupon_generator_instruction');
    }
}
