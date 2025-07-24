<?php

namespace Authorization\Dto;

use Olobase\Attribute\Input;
use Olobase\Attribute\CollectionInput;
use Olobase\Attribute\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Validator\Db\NoRecordExists;

#[InputFilter]
class RoleCreateDto
{
    #[Input(
        name: 'id',
        required: true,
        validators: [
            ['name' => Uuid::class],
            [
                'name' => NoRecordExists::class,
                'options' => [
                    'table'   => 'roles',
                    'field'   => 'id',
                    'adapter' => 'default',
                ]
            ],
        ]
    )]
    public string $id;

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
    public string $name;

    #[Input(
        name: 'level',
        required: true,
        filters: [
            ['name' => \Laminas\Filter\ToInt::class]
        ]
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
    public array $rolePermissions;
}
