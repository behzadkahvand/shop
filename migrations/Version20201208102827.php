<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201208102827 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventories ADD status VARCHAR(255) DEFAULT NULL AFTER final_price');
        $this->addSql('UPDATE inventories SET status = "CONFIRMED"');
        $this->addSql(sprintf(
            'INSERT INTO `configurations` (`code`, `value`, `created_at`, `updated_at`) VALUES ("CHECK_INITIAL_INVENTORY_STATUS", "false", "%1$s", "%1$s")',
            (new \DateTimeImmutable())->format("Y-m-d H:i:s")
        ));
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventories DROP status');
    }
}
