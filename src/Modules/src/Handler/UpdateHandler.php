<?php

declare(strict_types=1);

namespace Modules\Handler;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modules\InputFilter\SaveFilter;
use Modules\Model\ModuleModelInterface;
use Modules\Schema\ModuleSave;
use Olobase\DataTable\DataManagerInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/modules/update/:id',
    methods: ['PUT'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
    ]
)]
class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private ModuleModelInterface $moduleModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) {
    }

    #[OA\Put(
        path: '/modules/update/{id}',
        operationId: 'modules_update',
        summary: 'Update module',
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            description: 'Update role',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleSave')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/ModuleUpdateResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns to validation errors'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        $data     = [];
        $response = [];
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data     = $this->dataManager->getSaveData(ModuleSave::class, 'modules');
            $module   = $data['modules'];
            $moduleId = $this->filter->getValue('id');
            if ($module['name'] == 'Modules') {
                return new JsonResponse(
                    [
                        'data' => [
                            'info' => 'The core module `Modules` cannot be modified',
                        ],
                    ],
                    400
                );
            }
            $oldRow = $this->moduleModel->findOneById($moduleId);
            $this->moduleModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        $response['data']['oldRecord'] = $oldRow;
        return new JsonResponse($response);
    }
}
