<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Attribute\Route;
use Olobase\Mapper\OutputSchemaMapper;
use Authorization\Schema\RolesFindOneById;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Authorization\RoleModelInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/authorization/roles/findOneById/:id',
    methods: ['GET'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoleModelInterface $roleModel
    )
    {
    }

    #[OA\Get(
        path: '/authorization/roles/findOneById/{id}',
        tags: ['Authorization Roles'],
        summary: 'Find item data',
        operationId: 'authorizationRoles_findOneById',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/RolesFindOneById')
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $roleId = $request->getAttribute("id");
        $row = $this->roleModel->findOneById($roleId);
        if ($row) {
            $data = new OutputSchemaMapper(RolesFindOneById::class, $row);
            return new JsonResponse($data);   
        }
        return new JsonResponse([], 404);
    }

}
