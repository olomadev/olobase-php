<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Authorization\Dto\RoleCreateDto;
use Authorization\Entity\Role;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authorization\AuthorizationMiddleware;
use Olobase\Attribute\Route;
use Olobase\Authorization\Contract\RoleRepositoryInterface;
use Olobase\Filter\AttributeInputFilterCollector;
use Olobase\Mapper\InputSchemaMapper;
use Olobase\Util\ValidationErrorFormatterInterface;
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
        path: '/authorization/roles/create',
        tags: ['Authorization Roles'],
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
        $collector = new AttributeInputFilterCollector($this->filterManager);
        $filter    = $collector->fromObject(new RoleCreateDto(), $request->getParsedBody());
        if ($filter->isValid()) {
            $mapper = new InputSchemaMapper();
            $entity = $mapper->mapToEntity($filter, Role::class);
            $this->roleRepository->createEntity($entity);
        } else {
            return new JsonResponse($this->errorFormatter->format($filter), 400);
        }
        return new JsonResponse([]);
    }
}
