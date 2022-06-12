<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210919094158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create wallet and wallet_histories tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wallet_histories (id INT AUTO_INCREMENT NOT NULL, wallet_id INT NOT NULL, amount INT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_986DDE32712520F3 (wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallets (id INT AUTO_INCREMENT NOT NULL, balance INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wallet_histories ADD CONSTRAINT FK_986DDE32712520F3 FOREIGN KEY (wallet_id) REFERENCES wallets (id)');
        $this->addSql('ALTER TABLE customers ADD wallet_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21712520F3 FOREIGN KEY (wallet_id) REFERENCES wallets (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62534E21712520F3 ON customers (wallet_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21712520F3');
        $this->addSql('ALTER TABLE wallet_histories DROP FOREIGN KEY FK_986DDE32712520F3');
        $this->addSql('DROP TABLE wallet_histories');
        $this->addSql('DROP TABLE wallets');
        $this->addSql('DROP INDEX UNIQ_62534E21712520F3 ON customers');
        $this->addSql('ALTER TABLE customers DROP wallet_id');
    }
}
