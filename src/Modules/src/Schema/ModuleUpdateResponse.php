<?php

namespace Modules\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class ModuleUpdateResponse
{
    #[OA\Property(ref: '#/components/schemas/ModuleUpdateResponseObject')]
    public object $oldRecord;
}
