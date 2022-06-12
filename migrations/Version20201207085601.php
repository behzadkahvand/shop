<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201207085601 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE inventories CHANGE stock stock INT UNSIGNED NOT NULL, CHANGE max_purchase_per_order max_purchase_per_order INT UNSIGNED NOT NULL, CHANGE supplies_in supplies_in INT UNSIGNED NOT NULL, CHANGE order_count order_count INT UNSIGNED NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX seller_variant ON inventories (seller_id, variant_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP INDEX seller_variant ON inventories');
        $this->addSql('ALTER TABLE inventories CHANGE stock stock INT NOT NULL, CHANGE max_purchase_per_order max_purchase_per_order INT NOT NULL, CHANGE supplies_in supplies_in INT NOT NULL, CHANGE order_count order_count INT NOT NULL');
    }
}
