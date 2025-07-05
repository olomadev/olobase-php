<?php
declare(strict_types=1);

namespace Authorization\Model;

use Laminas\Paginator\Paginator;

interface UserRoleModelInterface
{
    /**
     * Assign new role to user
     * 
     * @param  string $userId user id
     * @param  string $roleId role id
     * @return void
     */
    public function assignRole(string $userId, string $roleId) : void;

    /**
     * Un assign role from selected user
     *
     * @param  string $userId user id
     * @param  string $roleId role id
     * @return void
     */
    public function unassignRole(string $userId, string $roleId) : void;

    /**
     * Find all users by pagination and "roleId"
     * 
     * @param  array  $get query string
     * @return Laminas\Paginator\Paginator
     */
    public function findAllByPaging(array $get) : Paginator;

}
