<?php

namespace Authentication\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TokenRequest',
    required: ['username', 'password'],
    properties: [
        new OA\Property(property: 'username', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string')
    ],
    type: 'object'
)]
class TokenRequest
{

}