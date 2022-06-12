<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213065843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items ADD retail_price_updated_by_id INT DEFAULT NULL after retail_price');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB07D5B4C80 FOREIGN KEY (retail_price_updated_by_id) REFERENCES admins (id)');
        $this->addSql('CREATE INDEX IDX_62809DB07D5B4C80 ON order_items (retail_price_updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items DROP retail_price_updated_by_id');
    }
}
