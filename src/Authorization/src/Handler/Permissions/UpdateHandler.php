<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\PermissionUpdateDto;
use Authorization\Entity\Permission;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Olobase\Attribute\Route;
use Olobase\Authorization\PermissionRepositoryInterface;
use Olobase\Filter\AttributeInputFilterCollector;
use Olobase\Mapper\InputSchemaMapper;
use Olobase\Validation\ValidationErrorFormatterInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/permissions/update/:id',
    methods: ['PUT'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
    ]
)]
class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private InputFilterPluginManager $filterManager,
        private ValidationErrorFormatterInterface $errorFormatter
    ) {
    }

    #[OA\Put(
        path: '/api/authorization/permissions/update/{id}',
        tags: ['Authorization Permissions'],
        summary: 'Update a permission',
        operationId: 'authorizationPermissions_update',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Permission UUID',
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update permission data',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PermissionUpdateDto')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation'
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns validation errors'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dto       = new PermissionUpdateDto();
        $collector = new AttributeInputFilterCollector($this->filterManager);
        $filter    = $collector->fromObject($dto, $request->getParsedBody());
        if ($filter->isValid()) {
            $mapper = new InputSchemaMapper();
            $entity = $mapper->mapToEntity($filter, $dto, Permission::class);
            $this->permissionRepository->updateEntity($entity);
        } else {
            return new JsonResponse($this->errorFormatter->format($filter), 400);
        }
        return new JsonResponse([]);
    }
}
