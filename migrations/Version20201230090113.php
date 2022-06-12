<?php

declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201230090113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sellers ADD mobile VARCHAR(15) DEFAULT NULL, ADD phone VARCHAR(255) DEFAULT NULL, ADD address LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AFFE6BEF3C7323E0 ON sellers (mobile)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_AFFE6BEF3C7323E0 ON sellers');
        $this->addSql('ALTER TABLE sellers DROP mobile, DROP phone, DROP address');
    }
}
