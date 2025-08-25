<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Olobase\Dto\AbstractDto;
use OpenApi\Attributes as OA;
use Authorization\Entity\Role;

#[OA\Schema(
    description: "Roles find by id response scheme"
)]
class RolesFindByIdDto extends AbstractDto
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/RoleDto')
    )]
    public array $data;

    public function __construct(Role $roleEntity)
    {
        $this->data = RoleDto::hydrate($roleEntity->toCamelCaseArray())->toArray();
    }
}
