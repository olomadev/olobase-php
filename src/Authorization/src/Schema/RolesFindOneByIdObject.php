<?php

namespace Authorization\Schema;

/**
 * @OA\Schema()
 */
class RolesFindOneByIdObject
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
    public $name;
    /**
     * @var string
     * @OA\Property()
     */
    public $key;
    /**
     * @var string
     * @OA\Property()
     */
    public $level;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="name",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="method",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="route",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="action",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $rolePermissions;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="firstname",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="lastname",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="isActive",
    *             type="number",
    *           ),
    *           @OA\Property(
    *             property="createdAt",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $roleUsers;
}
