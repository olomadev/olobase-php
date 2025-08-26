<?php

declare(strict_types=1);

namespace Authentication\Dto;

use Laminas\Filter\StringTrim;
use Laminas\Validator\EmailAddress;
use Modularity\Attribute\Input;
use Modularity\Attribute\InputFilter;

#[InputFilter]
class TokenRequestDto
{
    #[Input(
        name: 'username',
        required: true,
        filters: [
            ['name' => StringTrim::class],
        ],
        validators: [
            [
                'name'    => EmailAddress::class,
                'options' => ['useMxCheck' => false],
            ],
        ]
    )]
    public string $username;

    #[Input(
        name: 'password',
        required: true,
        filters: [
            ['name' => StringTrim::class],
        ]
    )]
    public string $password;
}
