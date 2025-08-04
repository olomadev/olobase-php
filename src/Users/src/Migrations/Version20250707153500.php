<?php

declare(strict_types=1);

namespace Users\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250707153500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default users demo records.';
    }

    public function up(Schema $schema): void
    {
        $createdAt = date("Y-m-d H:i:s");
        $password = '$argon2id$v=19$m=65536,t=4,p=1$Z2hNLklsNHIwbE9VUDlsbQ$kMsGiXHUYsjwmbnEibG+rGzKUSBvly1mUS1/fJsLrk0';

        // Kullanıcıyı ekle
        $this->addSql(
            "INSERT INTO users (id, email, password, firstname, lastname, created_at, updated_at, is_active, is_email_activated) 
             VALUES (:id, :email, :password, :firstname, :lastname, :created_at, :updated_at, :is_active, :is_email_activated)",
            [
                'id' => 'c13e550a-60ee-48d5-bf6e-ed29310640b2',
                'email' => 'demo@example.com',
                'password' => $password,
                'firstname' => 'Demo',
                'lastname' => 'Admin',
                'created_at' => $createdAt,
                'updated_at' => null,
                'is_active' => 1,
                'is_email_activated' => 1,
            ]
        );

        // Avatar verisini oku
        $blobData = file_get_contents(APP_ROOT.'/src/Users/assets/images/sample-avatar.png');

        // Avatarı ekle
        $this->addSql(
            "INSERT INTO user_avatars (user_id, mime_type, avatar_image) 
             VALUES (:user_id, :mime_type, :avatar_image)",
            [
                'user_id' => 'c13e550a-60ee-48d5-bf6e-ed29310640b2',
                'mime_type' => 'image/png',
                'avatar_image' => $blobData,
            ]
        );
    }


    public function down(Schema $schema): void
    {
        $this->addSql(
            "DELETE FROM user_avatars WHERE user_id = 'c13e550a-60ee-48d5-bf6e-ed29310640b2'"
        );
        $this->addSql(
            "DELETE FROM users WHERE id = 'c13e550a-60ee-48d5-bf6e-ed29310640b2'"
        );
    }
}
