<?php

declare(strict_types=1);

namespace Modules\Handler;

use Modules\Dto\ModuleCreateDto;
use Modules\Model\ModuleModelInterface;
use Olobase\Attribute\Route;
use Olobase\Filter\AttributeInputFilterCollector;
use Olobase\Mapper\InputSchemaMapper;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
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
        private InputFilterPluginManager $filterPluginManager,
        private Error $error,
    ) {
    }

    #[OA\Post(
        path: '/modules/create',
        operationId: 'modules_create',
        summary: 'Create a new module',
        tags: ['Modules'],
        requestBody: new OA\RequestBody(
            description: 'Create a new module',
            content: new OA\JsonContent(ref: '#/components/schemas/ModuleCreateDto'),
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
        $dto = new ModuleCreateDto();
        $collector = new AttributeInputFilterCollector($this->filterPluginManager);
        $filter = $collector->fromObject($dto, $request->getParsedBody());

        if ($filter->isValid()) {
            $mapper = new InputSchemaMapper();
            $data = $mapper->map($filter, $dto);

            $this->moduleModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($filter), 400);
        }
        return new JsonResponse($response);
    }
}
