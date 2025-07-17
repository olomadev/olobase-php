<?php

declare(strict_types=1);

namespace Modules\Handler;

use Modules\Model\ModuleModelInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/modules/findAll',
    methods: ['GET'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private ModuleModelInterface $moduleModel)
    {
    }

    #[OA\Get(
        path: '/modules/findAll',
        operationId: 'modules_findAll',
        summary: 'Find all modules',
        tags: ['Modules'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/ModulesFindAll')
            ),
            new OA\Response(
                response: 404,
                description: 'No result found'
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $data = $this->moduleModel->findAll($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
