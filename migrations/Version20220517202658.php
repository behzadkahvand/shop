<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220517202658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'modify notifications table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6000B0D3771530982D737AEF34E21C13 ON notifications (code, section, notification_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6000B0D3771530982D737AEF34E21C13 ON notifications');
        $this->addSql('ALTER TABLE notifications DROP created_at, DROP updated_at');
    }
}
