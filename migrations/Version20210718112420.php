<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210718112420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create order_cancel_reasons_apology table to associate cancel reasons with apologies';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_cancel_reason_apology (id INT AUTO_INCREMENT NOT NULL, order_cancel_reason_id INT NOT NULL, apology_id INT NOT NULL, UNIQUE INDEX UNIQ_EFE0678F8C0C5710 (order_cancel_reason_id), INDEX IDX_EFE0678F3556B58D (apology_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_cancel_reason_apology ADD CONSTRAINT FK_EFE0678F8C0C5710 FOREIGN KEY (order_cancel_reason_id) REFERENCES order_cancel_reasons (id)');
        $this->addSql('ALTER TABLE order_cancel_reason_apology ADD CONSTRAINT FK_EFE0678F3556B58D FOREIGN KEY (apology_id) REFERENCES apology (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_cancel_reason_apology');
    }
}
