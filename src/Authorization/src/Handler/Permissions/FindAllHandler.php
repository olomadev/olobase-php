<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Olobase\Attribute\Route;
use Olobase\Authorization\PermissionRepositoryInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/permissions/findAll',
    methods: ['GET'],
    middlewares: []
)]
#[OA\Get(
    path: "/authorization/permissions/findAll",
    tags: ["Authorization Permissions"],
    summary: "Find all permissions",
    operationId: "authorizationPermissions_findAll",
    responses: [
        new OA\Response(
            response: 200,
            description: "Successful operation",
            content: new OA\JsonContent(ref: "#/components/schemas/PermissionsFindAllDto")
        ),
        new OA\Response(
            response: 404,
            description: "No result found"
        ),
    ]
)]
class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get  = $request->getQueryParams();
        $data = $this->permissionRepository->findAllPermissions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }
}
