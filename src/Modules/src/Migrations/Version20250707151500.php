<?php

declare(strict_types=1);

namespace Modules\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250707151500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default module record.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO modules (id, name, version, is_active, is_core) 
             VALUES ('88748bef-695d-435b-a752-8a6e7608c220', 'Modules', '1.0.0', 1, 1)"
        );

    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('modules');
    }
}
