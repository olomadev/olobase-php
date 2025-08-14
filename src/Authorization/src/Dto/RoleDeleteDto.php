<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Uuid;
use Olobase\Attribute\Input;

#[InputFilter]
class RoleDeleteDto
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
}
