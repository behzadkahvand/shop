<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210501071946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE VIEW view_on_sale_products AS
SELECT JSON_EXTRACT(C.value, CONCAT('$[', Numbers.n - 1, '].id'))       AS tpi_id,
       JSON_EXTRACT(C.value, CONCAT('$[', Numbers.n - 1, '].priority')) AS priority
FROM (
         SELECT 1 AS n
         UNION
         SELECT 2 AS n
         UNION
         SELECT 3 AS n
         UNION
         SELECT 4 AS n
         UNION
         SELECT 5 AS n
         UNION
         SELECT 6 AS n
         UNION
         SELECT 7 AS n
         UNION
         SELECT 8 AS n
         UNION
         SELECT 9 AS n
         UNION
         SELECT 10 AS n
         UNION
         SELECT 11 AS n
         UNION
         SELECT 12 AS n
         UNION
         SELECT 13 AS n
         UNION
         SELECT 14 AS n
         UNION
         SELECT 15 AS n
         UNION
         SELECT 16 AS n
         UNION
         SELECT 17 AS n
         UNION
         SELECT 18 AS n
         UNION
         SELECT 19 AS n
         UNION
         SELECT 20 AS n
     ) Numbers
         INNER JOIN configurations C ON Numbers.n <= JSON_LENGTH(C.value)
where C.code = 'ON_SALE_PRODUCTS'"
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP VIEW view_on_sale_products');
    }
}
