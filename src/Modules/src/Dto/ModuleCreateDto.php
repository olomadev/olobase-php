<?php

declare(strict_types=1);

namespace Modules\Dto;

use Laminas\Filter\StringTrim;
use Laminas\Filter\ToInt;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid;
use Olobase\Attribute\Input;
use Olobase\Attribute\InputFilter;
use OpenApi\Attributes as OA;

#[InputFilter]
#[OA\Schema(
    schema: "ModuleCreateDto",
    title: "Module Create DTO",
    description: "Data transfer object used to create a new module",
    required: ["id", "name", "version"]
)]
class ModuleCreateDto
{
    #[Input(
        name: 'id',
        required: true,
        validators: [
            ['name' => Uuid::class],
            [
                'name'    => NoRecordExists::class,
                'options' => [
                    'table'   => 'modules',
                    'field'   => 'id',
                    'adapter' => 'default',
                ],
            ],
        ]
    )]
    #[OA\Property(
        property: "id",
        type: "string",
        format: "uuid",
        description: "Module's unique ID (UUID)"
    )]
    public string $id;

    #[Input(
        name: 'name',
        required: true,
        filters: [
            ['name' => StringTrim::class],
        ],
        validators: [
            [
                'name'    => StringLength::class,
                'options' => [
                    'encoding' => 'UTF-8',
                    'min'      => 3,
                    'max'      => 40,
                ],
            ],
        ]
    )]
    #[OA\Property(
        property: "name",
        type: "string",
        minLength: 3,
        maxLength: 40,
        description: "Module name"
    )]
    public string $name;

    #[Input(
        name: 'version',
        required: true,
        filters: [
            ['name' => StringTrim::class],
        ],
        validators: [
            [
                'name'    => StringLength::class,
                'options' => [
                    'encoding' => 'UTF-8',
                    'min'      => 3,
                    'max'      => 16,
                ],
            ],
        ]
    )]
    #[OA\Property(
        property: "version",
        type: "string",
        minLength: 3,
        maxLength: 16,
        description: "Module version (example: 1.0.0)"
    )]
    public string $version;

    #[Input(
        name: 'is_active',
        required: false,
        filters: [
            ['name' => ToInt::class],
        ]
    )]
    #[OA\Property(
        property: "is_active",
        type: "integer",
        nullable: true,
        description: "Is the module active? (1 = yes, 0 = no)"
    )]
    public ?int $isActive = null;
}
