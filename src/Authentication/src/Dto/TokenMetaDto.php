<?php

declare(strict_types=1);

namespace Authentication\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'TokenMeta', type: 'object')]
class TokenMetaDto
{
    #[OA\Property(type: 'string')]
    public string $tokenId;

    #[OA\Property(type: 'string', format: 'ipv4')]
    public string $ipAddress;

    #[OA\Property(type: 'string')]
    public string $deviceKey;

    #[OA\Property(type: 'string', format: 'date-time')]
    public string $expiresAt;

    public function __construct(string $tokenId, string $ipAddress, string $deviceKey, string $expiresAt)
    {
        $this->tokenId = $tokenId;
        $this->ipAddress = $ipAddress;
        $this->deviceKey = $deviceKey;
        $this->expiresAt = $expiresAt;
    }
}
