<?php

declare(strict_types=1);

namespace Users\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250707153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users and user_avatars tables, insert demo user.';
    }

    public function up(Schema $schema): void
    {
        // USERS TABLE
        $users = $schema->createTable('users');

        $users->addColumn('id', Types::STRING, ['length' => 36, 'notnull' => true]);
        $users->addColumn('email', Types::STRING, ['length' => 160, 'notnull' => false]);
        $users->addColumn('password', Types::STRING, ['length' => 255, 'notnull' => false]);
        $users->addColumn('firstname', Types::STRING, ['length' => 120, 'notnull' => false]);
        $users->addColumn('lastname', Types::STRING, ['length' => 120, 'notnull' => false]);
        $users->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $users->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $users->addColumn('is_active', Types::BOOLEAN, ['default' => true, 'notnull' => false]);
        $users->addColumn('is_email_activated', Types::BOOLEAN, ['default' => false, 'notnull' => false]);

        $users->setPrimaryKey(['id']);
        $users->addIndex(['id'], 'user_id');

        // USER_AVATARS TABLE
        $avatars = $schema->createTable('user_avatars');

        $avatars->addColumn('user_id', Types::STRING, ['length' => 36, 'notnull' => true]);
        $avatars->addColumn('mime_type', Types::STRING, ['length' => 60, 'notnull' => false]);
        $avatars->addColumn('avatar_image', Types::BLOB, ['notnull' => false]);

        $avatars->setPrimaryKey(['user_id']);

        // Foreign key with ON DELETE CASCADE
        $avatars->addForeignKeyConstraint(
            'users',       // foreign table name
            ['user_id'],   // local columns
            ['id'],        // foreign columns
            ['onDelete' => 'CASCADE'],
            'fk_user_avatar_user'
        );

    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user_avatars');
        $schema->dropTable('users');
    }
}
