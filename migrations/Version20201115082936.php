<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201115082936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE promotion_discount (id INT AUTO_INCREMENT NOT NULL, action_id INT NOT NULL, amount INT NOT NULL, subject_type VARCHAR(255) NOT NULL, subject_id INT NOT NULL, INDEX IDX_27B4104A9D32F035 (action_id), INDEX IDX_27B4104A23EDC87 (subject_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE promotion_discount ADD CONSTRAINT FK_27B4104A9D32F035 FOREIGN KEY (action_id) REFERENCES promotion_action (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE promotion_discount');
    }
}
