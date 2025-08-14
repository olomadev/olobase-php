<?php

namespace Users\Service;

use Users\Entity\User;
use Users\Repository\UserRepositoryInterface;

class UserService
{
    public function __construct(private UserRepositoryInterface $repository)
    {
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    public function registerUser(string $email, string $password): User
    {
        $user = new User(
            id: 0,
            email: $email,
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            roles: ['user']
        );
        $this->repository->save($user);
        return $user;
    }
}
