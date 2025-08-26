<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Modularity\Dto\AbstractDto;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'PermissionDto', type: 'object')]
class PermissionDto extends AbstractDto
{
    #[OA\Property(property: 'id', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property(property: 'module', type: 'string')]
    public string $module;

    #[OA\Property(property: 'name', type: 'string')]
    public string $name;

    #[OA\Property(
        type: 'object',
        property: 'action',
        properties: [
            new OA\Property(property: 'id', type: 'string'),
            new OA\Property(property: 'name', type: 'string')
        ]
    )]
    public array $action;

    #[OA\Property(
        type: 'object',
        property: 'method',
        properties: [
            new OA\Property(property: 'id', type: 'string'),
            new OA\Property(property: 'name', type: 'string')
        ]
    )]
    public array $method;

    #[OA\Property(property: 'route', type: 'string')]
    public string $route;
}
