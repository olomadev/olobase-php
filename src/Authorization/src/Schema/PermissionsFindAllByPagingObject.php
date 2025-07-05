<?php

namespace Authorization\Schema;

/**
 * @OA\Schema()
 */
class PermissionsFindAllByPagingObject
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
    /**
     * @var string
     * @OA\Property()
     */
    public $module;
    /**
     * @var string
     * @OA\Property()
     */
    public $name;
    /**
     * @var string
     * @OA\Property()
     */
    public $action;
    /**
     * @var string
     * @OA\Property()
     */
    public $route;
    /**
     * @var string
     * @OA\Property()
     */
    public $method;
}
