<?php

namespace Users\Schema\MyAccount;

/**
 * @OA\Schema()
 */
class MyAccountUpdatePassword
{
    /**
     * @var string
     * @OA\Property()
     */
    public $oldPassword;
    /**
     * @var string
     * @OA\Property()
     */
    public $newPassword;
}
