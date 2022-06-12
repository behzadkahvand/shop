<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211225081317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet_histories ADD order_id INT DEFAULT NULL, ADD reference_id VARCHAR(255) DEFAULT NULL, ADD reason VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE wallet_histories ADD CONSTRAINT FK_986DDE328D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');
        $this->addSql('CREATE INDEX IDX_986DDE328D9F6D38 ON wallet_histories (order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet_histories DROP FOREIGN KEY FK_986DDE328D9F6D38');
        $this->addSql('DROP INDEX IDX_986DDE328D9F6D38 ON wallet_histories');
        $this->addSql('ALTER TABLE wallet_histories DROP order_id, DROP reference_id, DROP reason');
    }
}
