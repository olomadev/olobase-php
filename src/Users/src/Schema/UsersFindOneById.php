<?php

namespace Users\Schema;

/**
 * @OA\Schema()
 */
class UsersFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/UsersFindOneByIdObject",
     * )
     */
    public $data;
}
