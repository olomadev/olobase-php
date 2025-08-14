<?php

namespace Authorization\Dto;

use Olobase\Attribute\Input;
use Olobase\Attribute\ObjectInput;
use Olobase\Attribute\InputFilter;
use Laminas\Validator\InArray;
use OpenApi\Attributes as OA;

#[InputFilter]
#[OA\Schema(
    schema: "PermissionCreateDto",
    required: ["id", "action", "method"],
    type: "object",
    description: "Permission creation data transfer object"
)]
class PermissionCreateDto
{
    #[Input(name: 'name')]
    #[OA\Property(
        property: "name",
        type: "string",
        description: "Human-readable permission name"
    )]
    public string $name;

    #[ObjectInput(
        name: 'action',
        fields: [
            [
                'name' => 'id',
                'required' => true,
                'validators' => [
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => ['create', 'delete', 'edit', 'list', 'show']
                        ]
                    ]
                ]
            ]
        ]
    )]
    #[OA\Property(
        property: "action",
        type: "object",
        required: ["id"],
        properties: [
            new OA\Property(
                property: "id",
                type: "string",
                enum: ["create", "delete", "edit", "list", "show"],
                description: "Action type"
            )
        ]
    )]
    public array $action;

    #[ObjectInput(
        name: 'method',
        fields: [
            [
                'name' => 'id',
                'required' => true,
                'validators' => [
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH']
                        ]
                    ]
                ]
            ]
        ]
    )]
    #[OA\Property(
        property: "method",
        type: "object",
        required: ["id"],
        properties: [
            new OA\Property(
                property: "id",
                type: "string",
                enum: ["GET", "POST", "PUT", "DELETE", "PATCH"],
                description: "HTTP method"
            )
        ]
    )]
    public array $method;
}
