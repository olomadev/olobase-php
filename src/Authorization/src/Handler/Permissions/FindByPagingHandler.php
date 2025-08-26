<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\PermissionsFindByPagingDto;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modularity\Attribute\Route;
use Modularity\Authorization\PermissionRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/permissions/findByPaging',
    methods: ['GET'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
    ]
)]
class FindByPagingHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {
    }

    #[OA\Get(
        path: '/api/authorization/permissions/findByPaging',
        tags: ['Authorization'],
        summary: 'Find all roles by pagination',
        operationId: 'authorizationRoles_findByPaging',
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: false,
                description: 'Search string',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Per page',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'sort',
                in: 'query',
                required: false,
                description: 'Order items',
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items()
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/PermissionsFindByPagingDto')
            ),
            new OA\Response(
                response: 404,
                description: 'No result found'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get     = $request->getQueryParams();
        $page    = empty($get['page']) ? 1 : (int) $get['page'];
        $perPage = empty($get['per_page']) ? 5 : (int) $get['per_page'];

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->permissionRepository->findByPaging($get);

        $page = $page < 1 ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        $dto = new PermissionsFindByPagingDto($paginator);
        return new JsonResponse($dto->toArray());
    }
}
