<?php

namespace Authorization\Schema;

/**
 * @OA\Schema()
 */
class UserRoleAssignment
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $userId;
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $roleId;
}
