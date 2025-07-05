<?php

namespace Authorization\Schema;

/**
 * @OA\Schema()
 */
class PermissionSave
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
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="string",
    * )
    */
    public $action;
    /**
     * @var string
     * @OA\Property()
     */
    public $route;
    /**
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="string",
    * )
    */
    public $method;
}
