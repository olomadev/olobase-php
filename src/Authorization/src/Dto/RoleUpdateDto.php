<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Laminas\Filter\ToInt;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid;
use Modularity\Attribute\CollectionInput;
use Modularity\Attribute\Input;
use Modularity\Attribute\InputFilter;

#[InputFilter]
class RoleUpdateDto
{
    #[Input(
        name: 'id',
        required: true,
        validators: [
            ['name' => Uuid::class],
            [
                'name'    => RecordExists::class,
                'options' => [
                    'table'   => 'roles',
                    'field'   => 'id',
                ],
            ],
        ]
    )]
    public string $id;

    #[Input(
        name: 'key',
        required: true,
        validators: [
            [
                'name'    => StringLength::class,
                'options' => ['encoding' => 'UTF-8', 'min' => 2, 'max' => 60],
            ],
        ]
    )]
    public string $key;

    #[Input(
        name: 'name',
        required: true,
        validators: [
            [
                'name'    => StringLength::class,
                'options' => ['encoding' => 'UTF-8', 'min' => 2, 'max' => 100],
            ],
        ]
    )]
    public string $name;

    #[Input(
        name: 'level',
        required: true,
        filters: [
            ['name' => ToInt::class],
        ]
    )]
    public int $level;

    #[CollectionInput(
        name: 'rolePermissions',
        fields: [
            [
                'name'       => 'id',
                'required'   => true,
                'validators' => [
                    ['name' => Uuid::class],
                ],
            ],
        ]
    )]
    public array $rolePermissions;
}
