<?php

namespace Authorization\Schema;

/**
 * @OA\Schema()
 */
class PermissionsFindAll
{
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
    *             property="module",
    *             type="string",
    *           ),
    *            @OA\Property(
    *             property="name",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="route",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="method",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="action",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
