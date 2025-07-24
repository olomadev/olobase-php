<?php

declare(strict_types=1);

namespace Modules\Dto;

use Olobase\Attribute\Input;
use Olobase\Attribute\InputFilter;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid;

#[InputFilter]
class ModuleCreateDto
{
    #[Input(
        name: 'id',
        required: true,
        validators: [
            ['name' => Uuid::class],
            [
                'name' => \Laminas\Validator\Db\NoRecordExists::class,
                'options' => [
                    'table'   => 'modules',
                    'field'   => 'id',
                    'adapter' => 'default', // veya dinamik olarak atanacaksa service-level çözülmeli
                ]
            ]
        ]
    )]
    public string $id;

    #[Input(
        name: 'name',
        required: true,
        filters: [
            ['name' => \Laminas\Filter\StringTrim::class],
        ],
        validators: [
            [
                'name' => StringLength::class,
                'options' => [
                    'encoding' => 'UTF-8',
                    'min' => 3,
                    'max' => 40,
                ]
            ]
        ]
    )]
    public string $name;

    #[Input(
        name: 'version',
        required: true,
        filters: [
            ['name' => \Laminas\Filter\StringTrim::class],
        ],
        validators: [
            [
                'name' => StringLength::class,
                'options' => [
                    'encoding' => 'UTF-8',
                    'min' => 3,
                    'max' => 16,
                ]
            ]
        ]
    )]
    public string $version;

    #[Input(
        name: 'is_active',
        required: false,
        filters: [
            ['name' => \Laminas\Filter\ToInt::class],
        ]
    )]
    public ?int $isActive = null;
}
