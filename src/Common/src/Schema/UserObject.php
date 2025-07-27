<?php

namespace Common\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class UserObject
{
    #[OA\Property(type: "string")]
    public string $id;

    #[OA\Property(type: "string")]
    public string $firstname;

    #[OA\Property(type: "string")]
    public string $lastname;

    #[OA\Property(
        type: "array",
        items: new OA\Items(type: "string")
    )]
    public array $roles;

    #[OA\Property(type: "string")]
    public string $email;
}
