<?php

declare(strict_types=1);

namespace Authorization\Entity;

use Olobase\Entity\AbstractEntity;

final class Role extends AbstractEntity
{
    public function __construct(
        ?string $id = null,
        private ?string $key = null,
        private ?string $name = null,
        private ?int $level = null,
        private ?array $permissions = [],
    ) {
        parent::__construct($id);
    }

    public function getKey(): string
    {
        return (string) $this->key;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function getLevel(): int
    {
        return (int) $this->level;
    }

    public function getPermissions(): array
    {
        return (array) $this->permissions;
    }
}
