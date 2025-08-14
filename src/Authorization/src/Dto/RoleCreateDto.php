<?php

namespace Authorization\Dto;

use Olobase\Attribute\Input;
use Olobase\Attribute\CollectionInput;
use Olobase\Attribute\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use OpenApi\Attributes as OA;

#[InputFilter]
#[OA\Schema(
    schema: "RoleCreateDto",
    required: ["id", "key", "name", "level", "rolePermissions"],
    type: "object",
    description: "Role creation data transfer object"
)]
class RoleCreateDto
{
    #[Input(
        name: 'key',
        required: true,
        validators: [
            [
                'name' => StringLength::class,
                'options' => ['encoding' => 'UTF-8', 'min' => 2, 'max' => 60],
            ]
        ]
    )]
    #[OA\Property(
        property: "key",
        type: "string",
        minLength: 2,
        maxLength: 60,
        description: "Unique role key"
    )]
    public string $key;

    #[Input(
        name: 'name',
        required: true,
        validators: [
            [
                'name' => StringLength::class,
                'options' => ['encoding' => 'UTF-8', 'min' => 2, 'max' => 100],
            ]
        ]
    )]
    #[OA\Property(
        property: "name",
        type: "string",
        minLength: 2,
        maxLength: 100,
        description: "Human-readable role name"
    )]
    public string $name;

    #[Input(
        name: 'level',
        required: true,
        filters: [
            ['name' => \Laminas\Filter\ToInt::class]
        ]
    )]
    #[OA\Property(
        property: "level",
        type: "integer",
        description: "Role hierarchy level"
    )]
    public int $level;

    #[CollectionInput(
        name: 'rolePermissions',
        fields: [
            [
                'name' => 'id',
                'required' => true,
                'validators' => [
                    ['name' => Uuid::class]
                ]
            ]
        ]
    )]
    #[OA\Property(
        property: "rolePermissions",
        type: "array",
        description: "List of permissions assigned to the role",
        items: new OA\Items(
            type: "object",
            properties: [
                new OA\Property(
                    property: "id",
                    type: "string",
                    format: "uuid",
                    description: "Permission ID"
                )
            ]
        )
    )]
    public array $roleP
