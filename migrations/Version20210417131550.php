<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210417131550 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_attribute_list_values DROP FOREIGN KEY FK_EA04F731F920BBA2');
        $this->addSql('DROP INDEX IDX_EA04F731F920BBA2 ON product_attribute_list_values');
        $this->addSql('ALTER TABLE product_attribute_list_values CHANGE value_id value INT NOT NULL');
        $this->addSql('ALTER TABLE product_attribute_list_values ADD CONSTRAINT FK_EA04F7311D775834 FOREIGN KEY (value) REFERENCES attribute_list_items (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_EA04F7311D775834 ON product_attribute_list_values (value)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_attribute_list_values DROP FOREIGN KEY FK_EA04F7311D775834');
        $this->addSql('DROP INDEX IDX_EA04F7311D775834 ON product_attribute_list_values');
        $this->addSql('ALTER TABLE product_attribute_list_values CHANGE value value_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_attribute_list_values ADD CONSTRAINT FK_EA04F731F920BBA2 FOREIGN KEY (value_id) REFERENCES attribute_list_items (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_EA04F731F920BBA2 ON product_attribute_list_values (value_id)');
    }
}
