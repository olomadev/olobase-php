<?php

namespace Common\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class ObjectId
{
    #[OA\Property(format: "uuid", type: "string")]
    public string $id;

    #[OA\Property(type: "string")]
    public string $name;
}
