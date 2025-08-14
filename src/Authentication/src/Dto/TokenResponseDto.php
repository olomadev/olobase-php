<?php

declare(strict_types=1);

namespace Authentication\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'TokenResponse', type: 'object')]
class TokenResponseDto
{
    #[OA\Property(type: 'string')]
    public string $token;

    #[OA\Property(ref: '#/components/schemas/TokenData')]
    public TokenDataDto $data;

    public function __construct(string $token, TokenDataDto $data)
    {
        $this->token = $token;
        $this->data  = $data;
    }
}
