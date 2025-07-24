<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Attribute\Route;
use Olobase\Filter\AttributeInputFilterCollector;
use Olobase\Authorization\Contracts\RoleModelInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/authorization/roles/delete/:id',
    methods: ['DELETE'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoleModelInterface $roleModel,        
        private InputFilterPluginManager $filterPluginManager,
        private Error $error,
    ) 
    {
    }
    
    #[OA\Delete(
        path: '/authorization/roles/delete/{id}',
        tags: ['Authorization Roles'],
        summary: 'Delete role',
        operationId: 'authorizationRoles_delete',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Role uuid',
                schema: new OA\Schema(
                    type: 'string',
                    format: 'uuid'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation'
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {   
        $dto = new RoleDeleteDto();
        $collector = new AttributeInputFilterCollector($this->filterPluginManager);
        $filter = $collector->fromObject($dto, $request->getQueryParams());
        if ($filter->isValid()) {
            $this->roleModel->delete(
                $filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->format($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
