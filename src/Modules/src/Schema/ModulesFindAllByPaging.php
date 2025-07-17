<?php

namespace Modules\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema]
class ModulesFindAllByPaging
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/ModulesFindAllByPagingObject')
    )]
    public array $data;

    #[OA\Property(property: "page", type: "integer")]
    public int $page;

    #[OA\Property(property: "per_page", type: "integer")]
    public int $perPage;

    #[OA\Property(property: "total_pages", type: "integer")]
    public int $totalPages;

    #[OA\Property(property: "total_items", type: "integer")]
    public int $totalItems;
}
