<?php

declare(strict_types=1);

namespace Modules\Service;

use Modules\Entity\Module;
use Modules\Repository\ModuleRepositoryInterface;

class ModuleService
{
    public function __construct(private ModuleRepositoryInterface $repository)
    {
    }

    public function getModuleById(string $id): ?Module
    {
        return $this->repository->findById($id);
    }

    public function listModules(): array
    {
        return $this->repository->findAll();
    }

    public function createModule(Module $module)
    {
        $this->repository->createEntity($module);
    }
}
