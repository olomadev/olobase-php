<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\PermissionCreateDto;
use Authorization\Entity\Permission;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modularity\Attribute\Entity;
use Modularity\Attribute\Route;
use Modularity\Authorization\PermissionRepositoryInterface;
use Modularity\Middleware\EntityMiddleware;
use Modularity\Validation\ValidationErrorFormatterInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/permissions/create',
    methods: ['POST'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
        EntityMiddleware::class,
    ]
)]
class CreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private InputFilterPluginManager $filterManager,
        private ValidationErrorFormatterInterface $errorFormatter,
    ) {
    }

    #[Entity(dto: PermissionCreateDto::class, entity: Permission::class)]
    #[OA\Post(
        path: '/api/authorization/permissions/create',
        tags: ['Authorization'],
        summary: 'Create a new permission',
        operationId: 'authorizationPermissions_create',
        requestBody: new OA\RequestBody(
            description: 'Create a new permission',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PermissionCreateDto')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'string',
                                    example: 'b8d3a570-3c3d-11ee-be56-0242ac120002'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns validation errors'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entity = $request->getAttribute('entity');
        $permId = $this->permissionRepository->createEntity($entity);

        return new JsonResponse(['data' => ['id' => $permId]]);
    }
}
