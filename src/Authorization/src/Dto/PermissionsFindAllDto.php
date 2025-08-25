<?php

declare(strict_types=1);

namespace Authorization\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: "Permissions find all response schema"
)]
class PermissionFindAllDto
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/PermissionDto')
    )]
    public array $data;
}
