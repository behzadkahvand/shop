<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211201114516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items ADD deleted_by_id INT DEFAULT NULL after deleted_at');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES admins (id)');
        $this->addSql('CREATE INDEX IDX_62809DB0C76F1F52 ON order_items (deleted_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0C76F1F52');
        $this->addSql('DROP INDEX IDX_62809DB0C76F1F52 ON order_items');
        $this->addSql('ALTER TABLE order_items DROP deleted_by_id');
    }
}
