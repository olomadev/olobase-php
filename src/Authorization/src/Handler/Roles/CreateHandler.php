<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Attribute\Route;
use Olobase\Filter\AttributeInputFilterCollector;
use Olobase\Mapper\InputSchemaMapper;
use Olobase\Authorization\Contract\RoleModelInterface;
use Authorization\Schema\RoleSave;
use Authorization\Dto\RoleCreateDto;
use Olobase\Util\ValidationErrorFormatterInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/authorization/roles/create',
    methods: ['POST'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class CreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoleModelInterface $roleModel,
        private InputFilterPluginManager $filterPluginManager,
        private Error $error,
    ) 
    {
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
                ref: '#/components/schemas/RoleSave'
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
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dto = new RoleCreateDto();
        $collector = new AttributeInputFilterCollector($this->filterPluginManager);
        $filter = $collector->fromObject($dto, $request->getParsedBody());
        if ($filter->isValid()) {
            $mapper = new InputSchemaMapper();
            $data = $mapper->map($filter, RoleSave::class);
            $this->roleModel->create($data);
        } else {
            return new JsonResponse($this->error->format($filter), 400);
        }
        return new JsonResponse([]);     
    }
}
