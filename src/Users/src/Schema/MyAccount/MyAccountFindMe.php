<?php

namespace Users\Schema\MyAccount;

/**
 * @OA\Schema()
 */
class MyAccountFindMe
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/MyAccountFindMeObject",
     * )
     */
    public $data;
}
