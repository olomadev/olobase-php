<?php

namespace Users\Schema;

/**
 * @OA\Schema()
 */
class UsersFindOneByIdObject
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
    public $firstname;
    /**
     * @var string
     * @OA\Property()
     */
    public $lastname;
    /**
     * @var string
     * @OA\Property()
     */
    public $email;
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
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/AvatarObject",
    * )
    */
    public $avatar;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $createdAt;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $updatedAt;
}
