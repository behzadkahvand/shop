<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211227104334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add before_amount and after_amount to wallet_histories table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet_histories ADD before_amount INT UNSIGNED DEFAULT 0 NOT NULL, ADD after_amount INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet_histories DROP before_amount, DROP after_amount');
    }
}
