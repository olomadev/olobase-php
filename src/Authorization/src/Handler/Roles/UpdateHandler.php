<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Attribute\Route;
use Olobase\Authorization\Contracts\RoleModelInterface;
use Authorization\Schema\RoleSave;
use Authorization\InputFilter\Roles\SaveFilter;
use Common\Helper\ValidationErrorFormatterInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/authorization/roles/update/:id',
    methods: ['PUT'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoleModelInterface $roleModel,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
    }
    
    #[OA\Put(
        path: '/authorization/roles/update/{id}',
        tags: ['Authorization Roles'],
        summary: 'Update role',
        operationId: 'authorizationRoles_update',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Update role',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RoleSave')
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
        $dto = new RoleUpdateDto();
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
