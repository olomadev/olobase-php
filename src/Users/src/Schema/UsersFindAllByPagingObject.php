<?php

namespace Users\Schema;

/**
 * @OA\Schema()
 */
class UsersFindAllByPagingObject
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
    public $email;
    /**
     * @var string
     * @OA\Property()
     */
    public $firstname;
    /**
     * @var string
     * @OA\Property()
     */
    public $lastname;
    /**
     * @var integer
     * @OA\Property()
     */
    public $isActive;
    /**
     * @var integer
     * @OA\Property()
     */
    public $isEmailActivated;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $updatedAt;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $createdAt;
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
    *     ),
    *  )
    */
    public $userRoles;
}
