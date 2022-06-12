<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210403081048 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seo_selected_filters (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, entity_name VARCHAR(255) NOT NULL, entity_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, meta_description VARCHAR(512) DEFAULT NULL, starred TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_DB116BDB12469DE2 (category_id), INDEX IDX_DB116BDB81257D5D (entity_id), UNIQUE INDEX seo_selected_filter (category_id, entity_name, entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE seo_selected_filters ADD CONSTRAINT FK_DB116BDB12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE seo_selected_filters ADD CONSTRAINT FK_DB116BDB81257D5D FOREIGN KEY (entity_id) REFERENCES brands (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE seo_selected_filters');
    }
}
