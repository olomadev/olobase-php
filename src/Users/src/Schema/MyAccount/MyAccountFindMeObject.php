<?php

namespace Users\Schema\MyAccount;

/**
 * @OA\Schema()
 */
class MyAccountFindMeObject
{
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
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/AvatarObject",
    * )
    */
    public $avatar;
}
