<?php

declare(strict_types=1);

namespace Users\Repository;

use Users\Entity\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
}
