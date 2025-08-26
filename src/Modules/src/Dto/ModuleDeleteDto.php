<?php

declare(strict_types=1);

namespace Modules\Dto;

use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Uuid;
use Modularity\Attribute\Input;
use Modularity\Attribute\InputFilter;
use OpenApi\Attributes as OA;

#[InputFilter]
#[OA\Schema(
    schema: "ModuleDeleteDto",
    title: "Module Delete DTO",
    description: "Data transfer object used to delete a module",
    required: ["id"]
)]
class ModuleDeleteDto
{
    #[Input(
        name: 'id',
        required: true,
        validators: [
            ['name' => Uuid::class],
            [
                'name'    => RecordExists::class,
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
}
