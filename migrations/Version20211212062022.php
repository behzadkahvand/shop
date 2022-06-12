<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211212062022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create seller_scores table and add score_id to sellers table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seller_scores (id INT AUTO_INCREMENT NOT NULL, return_score INT NOT NULL, delivery_delay_score INT NOT NULL, order_cancellation_score INT NOT NULL, total_score INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sellers ADD score_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sellers ADD CONSTRAINT FK_AFFE6BEF12EB0A51 FOREIGN KEY (score_id) REFERENCES seller_scores (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AFFE6BEF12EB0A51 ON sellers (score_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE seller_scores');
        $this->addSql('DROP INDEX UNIQ_AFFE6BEF12EB0A51 ON sellers');
        $this->addSql('ALTER TABLE sellers DROP score_id');
    }
}
