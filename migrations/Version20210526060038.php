<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526060038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE marketing_seller_landings_categories (marketing_seller_landing_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_DEC1580D9AF24C5F (marketing_seller_landing_id), INDEX IDX_DEC1580D12469DE2 (category_id), PRIMARY KEY(marketing_seller_landing_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories ADD CONSTRAINT FK_DEC1580D9AF24C5F FOREIGN KEY (marketing_seller_landing_id) REFERENCES marketing_seller_landings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE marketing_seller_landings_categories ADD CONSTRAINT FK_DEC1580D12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE marketing_seller_landings DROP FOREIGN KEY FK_1AB3B38512469DE2');
        $this->addSql('DROP INDEX IDX_1AB3B38512469DE2 ON marketing_seller_landings');
        $this->addSql('ALTER TABLE marketing_seller_landings DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE marketing_seller_landings_categories');
        $this->addSql('ALTER TABLE marketing_seller_landings ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE marketing_seller_landings ADD CONSTRAINT FK_1AB3B38512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_1AB3B38512469DE2 ON marketing_seller_landings (category_id)');
    }
}
