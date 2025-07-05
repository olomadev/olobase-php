<?php

namespace Modules\Schema;

/**
 * @OA\Schema()
 */
class ModulesFindAll
{
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ),
    *            @OA\Property(
    *             property="name",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="version",
    *             type="string",
    *           )
    *     ),
    *  )
    */
    public $data;
}
