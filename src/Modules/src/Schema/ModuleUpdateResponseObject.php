<?php

namespace Modules\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class ModuleUpdateResponseObject
{
    #[OA\Property(property: "id", format: 'uuid')]
    public string $id;

    #[OA\Property(property: "name", type: "string")]
    public string $name;

    #[OA\Property(property: "version", type: "string")]
    public string $version;

    #[OA\Property(property: "is_active", type: "integer")]
    public int $isActive;
}
