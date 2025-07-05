<?php

namespace Authorization\Schema;

/**
 * @OA\Schema()
 */
class RolesFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/RolesFindOneByIdObject",
     * )
     */
    public $data;
}
