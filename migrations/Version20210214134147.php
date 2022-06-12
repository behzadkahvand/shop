<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210214134147 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_addresses DROP INDEX UNIQ_D34D0EEE8D9F6D38, ADD INDEX IDX_D34D0EEE8D9F6D38 (order_id)');
        $this->addSql('ALTER TABLE order_addresses ADD is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER order_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_addresses DROP INDEX IDX_D34D0EEE8D9F6D38, ADD UNIQUE INDEX UNIQ_D34D0EEE8D9F6D38 (order_id)');
        $this->addSql('ALTER TABLE order_addresses DROP is_active');
    }
}
