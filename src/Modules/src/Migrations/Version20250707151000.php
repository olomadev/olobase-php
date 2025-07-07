<?php

declare(strict_types=1);

namespace Modules\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250707151000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the modules table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('modules');

        $table->addColumn('id', Types::STRING, [
            'length' => 36,
            'notnull' => true,
            'fixed' => true,
        ]);

        $table->addColumn('name', Types::STRING, [
            'length' => 40,
            'notnull' => true,
        ]);

        $table->addColumn('version', Types::STRING, [
            'length' => 16,
            'notnull' => true,
        ]);

        $table->addColumn('is_active', Types::BOOLEAN, [
            'default' => false,
        ]);

        $table->addColumn('is_core', Types::BOOLEAN, [
            'default' => false,
        ]);

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('modules');
    }
}
