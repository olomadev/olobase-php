<?php

namespace Users\Schema;

/**
 * @OA\Schema()
 */
class UserSave
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
    public $password;
    /**
     * @var string
     * @OA\Property()
     */
    public $email;
    /**
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/AvatarObject",
    * )
    */
    public $avatar;
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
}
