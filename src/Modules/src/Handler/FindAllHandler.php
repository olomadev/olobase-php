<?php

declare(strict_types=1);

namespace Modules\Handler;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modules\Model\ModuleModelInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/modules/findAll',
    methods: ['GET'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
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
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get  = $request->getQueryParams();
        $data = $this->moduleModel->findAll($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }
}
