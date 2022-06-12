<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201202093712 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_936C863DCAC822D9CBD071494B3656601B5771DD ON inventories');
        $this->addSql('DROP INDEX search_idx ON products');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A444839EA ON products (visits)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_936C863DCAC822D9CBD071494B3656601B5771DD ON inventories (price, final_price, stock, is_active)');
        $this->addSql('DROP INDEX IDX_B3BA5A5A444839EA ON products');
        $this->addSql('CREATE INDEX search_idx ON products (title, status, visits, order_count, is_original)');
    }
}
