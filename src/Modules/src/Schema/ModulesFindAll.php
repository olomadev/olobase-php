<?php

namespace Modules\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class ModulesFindAll
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'version', type: 'string'),
            ]
        )
    )]
    public array $data;
}
