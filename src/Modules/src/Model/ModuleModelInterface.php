<?php
declare(strict_types=1);

namespace Modules\Model;

use Laminas\Paginator\Paginator;

interface ModuleModelInterface
{    
    /**
     * Returns to all cached modules
     * 
     * @return array
     */
    public function findAll(): array;

    /**
     * Find all modules by pagination adapter
     * 
     * @param  array  $get query string
     * @return Paginator
     */
    public function findAllByPaging(array $get) : Paginator;

    /**
     * Find a module by its moduleId.
     *
     * @param string $moduleId
     * @return array module details
     */
    public function findOneById(string $moduleId);

    /**
     * Create a new module
     *
     * @param array $data Role and permission data
     * @return void
     */
    public function create(array $data) : void;

    /**
     * Update an existing module
     *
     * @param array $data Role and permission data
     * @return void
     */
    public function update(array $data) : void;

    /**
     * Delete a module by its moduleId.
     *
     * @param string $moduleId Module ID
     * @return void
     */
    public function delete(string $moduleId) : void;
}
