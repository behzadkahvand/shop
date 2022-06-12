<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201203151644 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_936C863D72B4E3F7 ON inventories (supplies_in)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A518597B1 ON products (subtitle)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_936C863D72B4E3F7 ON inventories');
        $this->addSql('DROP INDEX IDX_B3BA5A5A518597B1 ON products');
    }
}
