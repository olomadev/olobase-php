<?php

namespace Common\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class CommonFindAll
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
            ]
        )
    )]
    public array $data;
}
