<?php

declare(strict_types=1);

namespace Modules\Handler;

use Modules\Model\ModuleModelInterface;
use Modules\Schema\ModuleSave;
use Modules\InputFilter\SaveFilter;
use Olobase\DataTable\DataManagerInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/modules/update/:id',
    methods: ['PUT'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
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
            )
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
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getSaveData(ModuleSave::class, 'modules');
            $module = $data['modules'];
            $moduleId = $this->filter->getValue('id');
            if ($module['name'] == 'Modules') {
                return new JsonResponse(
                    [
                        'data' => [
                            'info' => 'The core module `Modules` cannot be modified',
                        ]
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
