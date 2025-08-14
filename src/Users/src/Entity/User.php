<?php

declare(strict_types=1);

namespace Users\Entity;

final class User
{
    public function __construct(
        public readonly int $id,
        public string $email,
        public string $passwordHash,
        public array $roles = [],
    ) {
    }
}
