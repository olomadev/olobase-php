<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Modularity\Dto\AbstractDto;
use OpenApi\Attributes as OA;
use Authorization\Entity\Role;

#[OA\Schema(
    description: "Permission find by id response scheme"
)]
class PermissionFindByIdDto extends AbstractDto
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/PermissionDto')
    )]
    public array $data;

    public function __construct(Permission $entity)
    {
        $this->data = Permission::hydrate($entity->toCamelCaseArray())->toArray();
    }
}
