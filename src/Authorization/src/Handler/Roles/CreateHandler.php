<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modularity\Attribute\Route;
use Modularity\Authorization\Contract\RoleRepositoryInterface;
use Modularity\Middleware\EntityMiddleware;
use Modularity\Util\ValidationErrorFormatterInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/authorization/roles/create',
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
        private RoleRepositoryInterface $roleRepository,
        private InputFilterPluginManager $filterPluginManager,
        private ValidationErrorFormatterInterface $errorFormatter,
    ) {
    }

    #[OA\Post(
        path: '/api/authorization/roles/create',
        tags: ['Authorization'],
        summary: 'Create a new role',
        operationId: 'authorizationRoles_create',
        requestBody: new OA\RequestBody(
            description: 'Create a new role',
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/RoleCreateDto'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation'
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns to validation errors'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entity = $request->getAttribute('entity');
        $roleId = $this->roleRepository->createEntity($entity);

        return new JsonResponse(['data' => ['id' => $roleId]]);
    }
}
