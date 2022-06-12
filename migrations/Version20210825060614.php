<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210825060614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create campaign_commissions table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE campaign_commissions (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, brand_id INT NOT NULL, seller_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, fee DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, terminated_at DATETIME DEFAULT NULL, INDEX IDX_A6684F3612469DE2 (category_id), INDEX IDX_A6684F3644F5D008 (brand_id), INDEX IDX_A6684F368DE820D9 (seller_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE campaign_commissions ADD CONSTRAINT FK_A6684F3612469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE campaign_commissions ADD CONSTRAINT FK_A6684F3644F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id)');
        $this->addSql('ALTER TABLE campaign_commissions ADD CONSTRAINT FK_A6684F368DE820D9 FOREIGN KEY (seller_id) REFERENCES sellers (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE campaign_commissions');
    }
}
