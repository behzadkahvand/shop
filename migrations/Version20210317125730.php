<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210317125730 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attribute_groups (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D28C172A2B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribute_list_items (id INT AUTO_INCREMENT NOT NULL, list_id INT NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_3CEB7A2B3DAE168B (list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attribute_lists (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_EB1E7C132B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attributes (id INT AUTO_INCREMENT NOT NULL, list_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, is_multiple TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_319B9E702B36786B (title), INDEX IDX_319B9E703DAE168B (list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_attribute_groups (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, attribute_group_id INT NOT NULL, priority INT DEFAULT NULL, INDEX IDX_57B1AEAE12469DE2 (category_id), INDEX IDX_57B1AEAE62D643B7 (attribute_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_attributes (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, attribute_group_id INT NOT NULL, attribute_id INT NOT NULL, priority INT NOT NULL, is_filter TINYINT(1) NOT NULL, is_required TINYINT(1) NOT NULL, INDEX IDX_1785CE0E12469DE2 (category_id), INDEX IDX_1785CE0E62D643B7 (attribute_group_id), INDEX IDX_1785CE0EB6E62EFA (attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_attribute_boolean_values (id BIGINT AUTO_INCREMENT NOT NULL, product_attribute_id BIGINT NOT NULL, value TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_FF28FCC03B420C91 (product_attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_attribute_list_values (id BIGINT AUTO_INCREMENT NOT NULL, value_id INT NOT NULL, product_attribute_id BIGINT NOT NULL, INDEX IDX_EA04F731F920BBA2 (value_id), INDEX IDX_EA04F7313B420C91 (product_attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_attribute_numeric_values (id BIGINT AUTO_INCREMENT NOT NULL, product_attribute_id BIGINT NOT NULL, value NUMERIC(10, 2) NOT NULL, UNIQUE INDEX UNIQ_295C20533B420C91 (product_attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_attribute_text_values (id BIGINT AUTO_INCREMENT NOT NULL, product_attribute_id BIGINT NOT NULL, value LONGTEXT NOT NULL, INDEX IDX_6658ACEC3B420C91 (product_attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_attributes (id BIGINT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, attribute_id INT NOT NULL, INDEX IDX_A2FCC15B4584665A (product_id), INDEX IDX_A2FCC15BB6E62EFA (attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attribute_list_items ADD CONSTRAINT FK_3CEB7A2B3DAE168B FOREIGN KEY (list_id) REFERENCES attribute_lists (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attributes ADD CONSTRAINT FK_319B9E703DAE168B FOREIGN KEY (list_id) REFERENCES attribute_lists (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE category_attribute_groups ADD CONSTRAINT FK_57B1AEAE12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE category_attribute_groups ADD CONSTRAINT FK_57B1AEAE62D643B7 FOREIGN KEY (attribute_group_id) REFERENCES attribute_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_attributes ADD CONSTRAINT FK_1785CE0E12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE category_attributes ADD CONSTRAINT FK_1785CE0E62D643B7 FOREIGN KEY (attribute_group_id) REFERENCES attribute_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_attributes ADD CONSTRAINT FK_1785CE0EB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_attribute_boolean_values ADD CONSTRAINT FK_FF28FCC03B420C91 FOREIGN KEY (product_attribute_id) REFERENCES product_attributes (id)');
        $this->addSql('ALTER TABLE product_attribute_list_values ADD CONSTRAINT FK_EA04F731F920BBA2 FOREIGN KEY (value_id) REFERENCES attribute_list_items (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_attribute_list_values ADD CONSTRAINT FK_EA04F7313B420C91 FOREIGN KEY (product_attribute_id) REFERENCES product_attributes (id)');
        $this->addSql('ALTER TABLE product_attribute_numeric_values ADD CONSTRAINT FK_295C20533B420C91 FOREIGN KEY (product_attribute_id) REFERENCES product_attributes (id)');
        $this->addSql('ALTER TABLE product_attribute_text_values ADD CONSTRAINT FK_6658ACEC3B420C91 FOREIGN KEY (product_attribute_id) REFERENCES product_attributes (id)');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT FK_A2FCC15B4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_attributes ADD CONSTRAINT FK_A2FCC15BB6E62EFA FOREIGN KEY (attribute_id) REFERENCES attributes (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_attribute_groups DROP FOREIGN KEY FK_57B1AEAE62D643B7');
        $this->addSql('ALTER TABLE category_attributes DROP FOREIGN KEY FK_1785CE0E62D643B7');
        $this->addSql('ALTER TABLE product_attribute_list_values DROP FOREIGN KEY FK_EA04F731F920BBA2');
        $this->addSql('ALTER TABLE attribute_list_items DROP FOREIGN KEY FK_3CEB7A2B3DAE168B');
        $this->addSql('ALTER TABLE attributes DROP FOREIGN KEY FK_319B9E703DAE168B');
        $this->addSql('ALTER TABLE category_attributes DROP FOREIGN KEY FK_1785CE0EB6E62EFA');
        $this->addSql('ALTER TABLE product_attributes DROP FOREIGN KEY FK_A2FCC15BB6E62EFA');
        $this->addSql('ALTER TABLE product_attribute_boolean_values DROP FOREIGN KEY FK_FF28FCC03B420C91');
        $this->addSql('ALTER TABLE product_attribute_list_values DROP FOREIGN KEY FK_EA04F7313B420C91');
        $this->addSql('ALTER TABLE product_attribute_numeric_values DROP FOREIGN KEY FK_295C20533B420C91');
        $this->addSql('ALTER TABLE product_attribute_text_values DROP FOREIGN KEY FK_6658ACEC3B420C91');
        $this->addSql('DROP TABLE attribute_groups');
        $this->addSql('DROP TABLE attribute_list_items');
        $this->addSql('DROP TABLE attribute_lists');
        $this->addSql('DROP TABLE attributes');
        $this->addSql('DROP TABLE category_attribute_groups');
        $this->addSql('DROP TABLE category_attributes');
        $this->addSql('DROP TABLE product_attribute_boolean_values');
        $this->addSql('DROP TABLE product_attribute_list_values');
        $this->addSql('DROP TABLE product_attribute_numeric_values');
        $this->addSql('DROP TABLE product_attribute_text_values');
        $this->addSql('DROP TABLE product_attributes');
    }
}
