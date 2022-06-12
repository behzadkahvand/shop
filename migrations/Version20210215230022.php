<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210215230022 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_addresses ADD is_foreigner TINYINT(1) DEFAULT \'0\' NOT NULL, ADD pervasive_code VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE customers ADD is_foreigner TINYINT(1) DEFAULT \'0\' NOT NULL, ADD pervasive_code VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_addresses ADD is_foreigner TINYINT(1) DEFAULT \'0\' NOT NULL, ADD pervasive_code VARCHAR(16) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_addresses DROP is_foreigner, DROP pervasive_code');
        $this->addSql('ALTER TABLE customers DROP is_foreigner, DROP pervasive_code');
        $this->addSql('ALTER TABLE order_addresses DROP is_foreigner, DROP pervasive_code');
    }
}
