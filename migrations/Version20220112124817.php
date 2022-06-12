<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112124817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove old tables after migrate data.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE category_commissions');
        $this->addSql('DROP TABLE category_leads');
        $this->addSql('DROP TABLE search_log');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_commissions (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, fee DOUBLE PRECISION NOT NULL, created_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C93931A712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE category_leads (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, value INT NOT NULL, created_at DATETIME NOT NULL, created_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_FFA30A1B12469DE3 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE search_log (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, term VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, result_count INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4B841C7A9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE category_commissions ADD CONSTRAINT FK_C93931A712469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE category_leads ADD CONSTRAINT FK_FFA30A1B12469DE3 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE search_log ADD CONSTRAINT FK_4B841C7A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
    }
}
