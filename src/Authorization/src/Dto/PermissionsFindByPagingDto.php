<?php

declare(strict_types=1);

namespace Authorization\Dto;

use Laminas\Paginator\Paginator;
use Olobase\Dto\AbstractDto;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'PermissionsFindByPagingDto', type: 'object')]
class PermissionsFindByPagingDto extends AbstractDto
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/PermissionDto')
    )]
    public array $data = [];

    #[OA\Property(type: 'integer')]
    public int $page = 0;

    #[OA\Property(type: 'integer')]
    public int $perPage = 0;

    #[OA\Property(type: 'integer')]
    public int $totalPages = 0;

    #[OA\Property(type: 'integer')]
    public int $totalItems = 0;

    public function __construct(?Paginator $paginator = null)
    {
        if ($paginator === null) {
            return;
        }

        $items = [];
        foreach ($paginator->getCurrentItems() as $row) {
            $items[] = PermissionDto::hydrate((array) $row)->toArray();
        }

        $this->data       = $items;
        $this->page       = $paginator->getCurrentPageNumber();
        $this->perPage    = $paginator->getItemCountPerPage();
        $this->totalPages = $paginator->count();
        $this->totalItems = $paginator->getTotalItemCount();
    }
}
