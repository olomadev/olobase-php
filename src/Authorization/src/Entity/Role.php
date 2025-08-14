<?php

declare(strict_types=1);

namespace Authorization\Entity;

use Olobase\Entity\AbstractEntity;

final class Role extends AbstractEntity
{
    public function __construct(
        ?string $id = null,
        private readonly ?string $key = null,
        private readonly ?string $name = null,
        private readonly ?int $level = null,
        private readonly ?array $rolePermissions = [],
    ) {
        parent::__construct($id);
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setRolePermissions(array $rolePermissions)
    {
        $this->rolePermissions = $rolePermissions;
    }

    public function getRolePermissions(): ?array
    {
        return $this->rolePermissions;
    }

}
