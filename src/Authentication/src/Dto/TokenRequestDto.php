<?php

namespace Authentication\Dto;

use Olobase\Attribute\Input;
use Olobase\Attribute\InputFilter;

#[InputFilter]
class TokenRequestDto
{
    #[Input(
        name: 'username',
        required: true,
        filters: [
            ['name' => \Laminas\Filter\StringTrim::class]
        ],
        validators: [
            [
                'name' => \Laminas\Validator\EmailAddress::class,
                'options' => ['useMxCheck' => false]
            ]
        ]
    )]
    public string $username;

    #[Input(
        name: 'password',
        required: true,
        filters: [
            ['name' => \Laminas\Filter\StringTrim::class]
        ]
    )]
    public string $password;
}
