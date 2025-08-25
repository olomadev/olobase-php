<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Uuid;
use Olobase\Attribute\Input;
use Olobase\Attribute\InputFilter;
use OpenApi\Attributes as OA;

#[InputFilter]
#[OA\Schema(
    schema: "PermissionDeleteDto",
    required: ["id"],
    type: "object",
    description: "Permission deletion data transfer object"
)]
class PermissionDeleteDto
{
    #[Input(
        name: 'id',
        required: true,
        validators: [
            [
                'name' => Uuid::class,
            ],
            [
                'name'    => RecordExists::class,
                'options' => [
                    'table' => 'permissions',
                    'field' => 'id',
                ],
            ],
        ]
    )]
    #[OA\Property(
        property: "id",
        type: "string",
        format: "uuid",
        description: "Permission UUID that must exist in the database"
    )]
    public string $id;
}
