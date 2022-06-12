<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201213111026 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products ADD buy_box_id INT DEFAULT NULL AFTER seller_id');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A83FBF3D8 FOREIGN KEY (buy_box_id) REFERENCES inventories (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3BA5A5A83FBF3D8 ON products (buy_box_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A83FBF3D8');
        $this->addSql('DROP INDEX UNIQ_B3BA5A5A83FBF3D8 ON products');
        $this->addSql('ALTER TABLE products DROP buy_box_id');
    }
}
