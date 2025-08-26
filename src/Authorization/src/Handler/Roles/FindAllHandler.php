<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Modularity\Attribute\Route;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Modularity\Authorization\Contract\RoleModelInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/authorization/roles/findAll',
    methods: ['GET'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private RoleModelInterface $roleModel)
    {
    }

    #[OA\Get(
        path: '/api/authorization/roles/findAll',
        tags: ['Authorization'],
        summary: 'Find all roles',
        operationId: 'authorizationRoles_findAll',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/CommonFindAll')
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
        $data = $this->roleModel->findAll($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
