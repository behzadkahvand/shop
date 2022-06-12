<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210215093101 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_legal_accounts DROP INDEX UNIQ_87539DD8D9F6D38, ADD INDEX IDX_87539DD8D9F6D38 (order_id)');
        $this->addSql('ALTER TABLE order_legal_accounts ADD is_active TINYINT(1) DEFAULT \'1\' NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_legal_accounts DROP INDEX IDX_87539DD8D9F6D38, ADD UNIQUE INDEX UNIQ_87539DD8D9F6D38 (order_id)');
        $this->addSql('ALTER TABLE order_legal_accounts DROP is_active, DROP created_at, DROP updated_at');
    }
}
