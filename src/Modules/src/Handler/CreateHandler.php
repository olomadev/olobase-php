<?php

declare(strict_types=1);

namespace Modules\Handler;

use Authentication\Middleware\JwtAuthenticationMiddleware;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authorization\AuthorizationMiddleware;
use Modules\Dto\ModuleCreateDto;
use Modules\Service\ModuleService;
use Olobase\Attribute\Route;
use Olobase\Filter\AttributeInputFilterCollector;
use Olobase\Mapper\InputSchemaMapper;
use Olobase\Util\RandomStringHelper;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/modules/create',
    methods: ['POST'],
    middlewares: [
        JwtAuthenticationMiddleware::class,
        AuthorizationMiddleware::class,
    ]
)]
class CreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private ModuleService $moduleService,
        private InputFilterPluginManager $filterManager,
        private ValidationErrorFormatterInterface $errorFormatter
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
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dto       = new ModuleCreateDto();
        $collector = new AttributeInputFilterCollector($this->filterManager);
        $filter    = $collector->fromObject($dto, $request->getParsedBody());

        if ($filter->isValid()) {
            $mapper = new InputSchemaMapper();
            $data   = $mapper->map($filter, $dto)['module'] ?? [];

            $module = new Module(
                id: RandomStringHelper::generateUuid(),
                name: $data['name'],
                version: $data['version'],
                isActive: (bool) $data['is_active']
            );
            $this->moduleService->createModule($module);
        } else {
            return new JsonResponse($this->error->getMessages($filter), 400);
        }
        return new JsonResponse($response);
    }
}
