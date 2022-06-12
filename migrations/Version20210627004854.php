<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210627004854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE normal_seller_scores DROP INDEX UNIQ_E0F41E5E504BEDAC, ADD INDEX IDX_E0F41E5E504BEDAC (seller_order_item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE normal_seller_scores DROP INDEX IDX_E0F41E5E504BEDAC, ADD UNIQUE INDEX UNIQ_E0F41E5E504BEDAC (seller_order_item_id)');
    }
}
