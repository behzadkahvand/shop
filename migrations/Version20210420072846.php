<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210420072846 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_list_items DROP FOREIGN KEY FK_3CEB7A2B3DAE168B');
        $this->addSql('ALTER TABLE attribute_list_items ADD CONSTRAINT FK_3CEB7A2B3DAE168B FOREIGN KEY (list_id) REFERENCES attribute_lists (id)');
        $this->addSql('ALTER TABLE product_attribute_list_values DROP FOREIGN KEY FK_EA04F7311D775834');
        $this->addSql('ALTER TABLE product_attribute_list_values ADD CONSTRAINT FK_EA04F7311D775834 FOREIGN KEY (value) REFERENCES attribute_list_items (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_list_items DROP FOREIGN KEY FK_3CEB7A2B3DAE168B');
        $this->addSql('ALTER TABLE attribute_list_items ADD CONSTRAINT FK_3CEB7A2B3DAE168B FOREIGN KEY (list_id) REFERENCES attribute_lists (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_attribute_list_values DROP FOREIGN KEY FK_EA04F7311D775834');
        $this->addSql('ALTER TABLE product_attribute_list_values ADD CONSTRAINT FK_EA04F7311D775834 FOREIGN KEY (value) REFERENCES attribute_list_items (id) ON DELETE CASCADE');
    }
}
