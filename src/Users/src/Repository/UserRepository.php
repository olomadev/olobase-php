<?php

declare(strict_types=1);

namespace Users\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Users\Entity\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private AdapterInterface $adapter)
    {
    }

    public function findById(int $id): ?User
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select('users')->where(['id' => $id]);

        $stmt = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute()->current();

        if (!$result) {
            return null;
        }

        return new User(
            id: (int) $result['id'],
            email: $result['email'],
            passwordHash: $result['password_hash'],
            roles: json_decode($result['roles'], true) ?? [],
        );
    }

    // findByEmail ve save metodlarını benzer şekilde eklersin
}
