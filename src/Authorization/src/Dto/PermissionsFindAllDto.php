<?php

namespace Authorization\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: "Permissions find all response schema"
)]
class PermissionFindAllDto
{
    #[OA\Property(
        type: "array",
        items: new OA\Items(
            properties: [
                new OA\Property(property: "id", type: "string"),
                new OA\Property(property: "module", type: "string"),
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "route", type: "string"),
                new OA\Property(property: "method", type: "string"),
                new OA\Property(property: "action", type: "string"),
            ]
        )
    )]
    public array $data;
}
