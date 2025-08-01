<?php

namespace Common\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class ObjectId
{
    #[OA\Property(property: 'id', format: 'uuid', type: 'string')]
    public string $id;

    #[OA\Property(property: 'id', type: 'string')]
    public string $name;
}
