<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Modularity\Dto\AbstractDto;
use OpenApi\Attributes as OA;
use Authorization\Entity\Role;

#[OA\Schema(
    description: "Role find by id response scheme"
)]
class RolesFindByIdDto extends AbstractDto
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/RoleDto')
    )]
    public array $data;

    public function __construct(Role $entity)
    {
        $this->data = RoleDto::hydrate($entity->toCamelCaseArray())->toArray();
    }
}
