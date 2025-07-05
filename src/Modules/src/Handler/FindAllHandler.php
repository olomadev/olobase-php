<?php

declare(strict_types=1);

namespace Modules\Handler;

use Modules\Model\ModuleModelInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private ModuleModelInterface $moduleModel)
    {
    }

    /**
     * @OA\Get(
     *   path="/modules/findAll",
     *   tags={"Modules"},
     *   summary="Find all modules",
     *   operationId="modules_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/ModulesFindAll"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $data = $this->moduleModel->findAll($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
