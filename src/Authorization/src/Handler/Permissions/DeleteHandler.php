<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\PermissionDeleteDto;
use Authorization\Entity\Permission;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authorization\AuthorizationMiddleware;
use Olobase\Attribute\Entity;
use Olobase\Attribute\Route;
use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\Middleware\EntityMiddleware;
use Olobase\Validation\ValidationErrorFormatterInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/permissions/delete/:id',
    methods: ['DELETE'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
        EntityMiddleware::class,
    ]
)]
class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private InputFilterPluginManager $filterManager,
        private ValidationErrorFormatterInterface $errorFormatter,
    ) {
    }

    #[Entity(dto: PermissionDeleteDto::class, entity: Permission::class)]
    #[OA\Delete(
        path: '/api/authorization/permissions/delete/{id}',
        tags: ['Authorization'],
        summary: 'Delete permission',
        operationId: 'authorizationPermissions_delete',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Permission uuid',
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entity = $request->getAttribute('entity');
        $permId = $this->permissionRepository->deleteEntity($entity);

        return new JsonResponse([]);
    }
}
