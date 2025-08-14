<?php

declare(strict_types=1);

namespace Authentication\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'TokenData', type: 'object')]
class TokenDataDto
{
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public array $roles;

    #[OA\Property(type: 'object')]
    public array $details;

    #[OA\Property(ref: '#/components/schemas/TokenMeta')]
    public TokenMetaDto $meta;

    #[OA\Property(type: 'object')]
    public array $extra;

    public function __construct(array $roles, array $details, TokenMetaDto $meta, array $extra = [])
    {
        $this->roles   = $roles;
        $this->details = $details;
        $this->meta    = $meta;
        $this->extra   = $extra;
    }
}
