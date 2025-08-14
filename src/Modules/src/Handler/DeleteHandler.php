<?php

declare(strict_types=1);

namespace Modules\Handler;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modules\InputFilter\DeleteFilter;
use Modules\Model\ModuleModelInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/modules/delete/:id',
    methods: ['DELETE'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
    ]
)]
class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private ModuleModelInterface $roleModel,
        private DeleteFilter $filter,
        private Error $error,
    ) {
    }

    #[OA\Delete(
        path: '/modules/delete/{id}',
        operationId: 'modules_delete',
        summary: 'Delete module',
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Module uuid',
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
        $this->filter->setInputData($request->getQueryParams());
        if ($this->filter->isValid()) {
            $this->moduleModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
