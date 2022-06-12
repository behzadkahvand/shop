<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210420115421 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_attribute_groups DROP FOREIGN KEY FK_57B1AEAE62D643B7');
        $this->addSql('ALTER TABLE category_attribute_groups ADD CONSTRAINT FK_57B1AEAE62D643B7 FOREIGN KEY (attribute_group_id) REFERENCES attribute_groups (id)');
        $this->addSql('ALTER TABLE category_attributes DROP FOREIGN KEY FK_1785CE0E62D643B7');
        $this->addSql('ALTER TABLE category_attributes DROP FOREIGN KEY FK_1785CE0EB6E62EFA');
        $this->addSql('ALTER TABLE category_attributes ADD CONSTRAINT FK_1785CE0E62D643B7 FOREIGN KEY (attribute_group_id) REFERENCES attribute_groups (id)');
        $this->addSql('ALTER TABLE category_attributes ADD CONSTRAINT FK_1785CE0EB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_attribute_groups DROP FOREIGN KEY FK_57B1AEAE62D643B7');
        $this->addSql('ALTER TABLE category_attribute_groups ADD CONSTRAINT FK_57B1AEAE62D643B7 FOREIGN KEY (attribute_group_id) REFERENCES attribute_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_attributes DROP FOREIGN KEY FK_1785CE0E62D643B7');
        $this->addSql('ALTER TABLE category_attributes DROP FOREIGN KEY FK_1785CE0EB6E62EFA');
        $this->addSql('ALTER TABLE category_attributes ADD CONSTRAINT FK_1785CE0E62D643B7 FOREIGN KEY (attribute_group_id) REFERENCES attribute_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_attributes ADD CONSTRAINT FK_1785CE0EB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE');
    }
}
