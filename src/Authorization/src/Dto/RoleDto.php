<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Modularity\Dto\AbstractDto;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'RoleDto', type: 'object')]
class RoleDto extends AbstractDto
{
    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property(property: 'key', type: 'string')]
    public string $key;

    #[OA\Property(property: 'name', type: 'string')]
    public string $name;

    #[OA\Property(property: 'level', type: 'integer')]
    public string $level;

    #[OA\Property(
        property: 'permissions',
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/PermissionDto')
    )]
    public array $permissions = [];
}
