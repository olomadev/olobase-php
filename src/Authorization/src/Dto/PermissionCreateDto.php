<?php

namespace Authorization\Dto;

use Olobase\Attribute\Input;
use Olobase\Attribute\ObjectInput;
use Olobase\Attribute\InputFilter;
use Laminas\Validator\InArray;

#[InputFilter]
class PermissionCreateDto
{
    #[Input(
        name: 'id',
        required: true,
        validators: [
            ['name' => \Laminas\Validator\Uuid::class]
        ]
    )]
    public string $id;

    #[Input(name: 'name')]
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
    public array $method;
}
