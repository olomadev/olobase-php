<?php

declare(strict_types=1);

namespace Modules\Repository;

use Modules\Entity\Module;
use Laminas\Paginator\Paginator;
use Olobase\Repository\CrudRepositoryInterface;

interface ModuleRepositoryInterface extends CrudRepositoryInterface
{
    public function findAll(): array;

    public function findAllByPaging(array $query): Paginator;

    public function findById(string $id): ?Module;
}
