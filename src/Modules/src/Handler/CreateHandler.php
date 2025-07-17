<?php

declare(strict_types=1);

namespace Modules\Handler;

use Common\Attribute\Route;
use Modules\Model\ModuleModelInterface;
use Modules\Schema\ModuleSave;
use Modules\InputFilter\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/modules/create',
    methods: ['POST'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class CreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private ModuleModelInterface $moduleModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
    }
    
    #[OA\Post(
        path: '/modules/create',
        operationId: 'modules_create',
        summary: 'Create a new module',
        tags: ['Modules'],
        requestBody: new OA\RequestBody(
            description: 'Create a new role',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleSave'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns to validation errors'
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {

            $data = $this->filter->getSchemaValues(ModuleSave::class);

            // $this->dataManager->setInputFilter($this->filter);
            // $data = $this->dataManager->getSaveData(ModuleSave::class, 'modules');
            $this->moduleModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);     
    }
}
