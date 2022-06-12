<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202101207 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_936C863D1B5771DD4B365660 ON inventories (is_active, stock)');
        $this->addSql('CREATE INDEX IDX_936C863DCBD07149 ON inventories (final_price)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_936C863D1B5771DD4B365660 ON inventories');
        $this->addSql('DROP INDEX IDX_936C863DCBD07149 ON inventories');
    }
}
