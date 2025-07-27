<?php

namespace Common\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class AvatarObject
{
    #[OA\Property(type: 'string')]
    public string $image;
}
