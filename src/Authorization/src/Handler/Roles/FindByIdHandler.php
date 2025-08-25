<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\RolesFindByIdDto;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Olobase\Attribute\Route;
use Olobase\Authorization\RoleRepositoryInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/roles/findById/:id',
    methods: ['GET'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
    ]
)]
class FindByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {
    }

    #[OA\Get(
        path: '/api/authorization/roles/findById/{id}',
        tags: ['Authorization'],
        summary: 'Find item data',
        operationId: 'authorizationRoles_findById',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/RolesFindById')
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $roleEntity = $this->roleRepository->findById($request->getAttribute("id"));
        
        if ($roleEntity) {
            $dto = new RolesFindByIdDto($roleEntity);
            return new JsonResponse($dto->toArray());
        }

        return new JsonResponse([], 404);
    }
}
