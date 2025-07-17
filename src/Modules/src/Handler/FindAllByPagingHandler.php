<?php

declare(strict_types=1);

namespace Modules\Handler;

use Common\Helper\JsonHelper;
use Modules\Model\ModuleModelInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/modules/findAllByPaging',
    methods: ['GET'],
    middlewares: [
        \Authentication\Middleware\JwtAuthenticationMiddleware::class,
        \Mezzio\Authorization\AuthorizationMiddleware::class
    ]
)]
class FindAllByPagingHandler implements RequestHandlerInterface
{
    public function __construct(private ModuleModelInterface $moduleModel)
    {
    }

    #[OA\Get(
        path: '/modules/findAllByPaging',
        operationId: 'modules_findAllByPaging',
        summary: 'Find all modules by pagination',
        tags: ['Modules'],
        parameters: [
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: false,
                description: 'Search string',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: '_page',
                in: 'query',
                required: false,
                description: 'Page number',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: '_perPage',
                in: 'query',
                required: false,
                description: 'Per page',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: '_sort',
                in: 'query',
                required: false,
                description: 'Order items',
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items()
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/ModulesFindAllByPaging')
            ),
            new OA\Response(
                response: 404,
                description: 'No result found'
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $page = empty($get['_page']) ? 1 : (int)$get['_page'];
        $perPage = empty($get['_perPage']) ? 5 : (int)$get['_perPage'];

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->moduleModel->findAllByPaging($get);

        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return new JsonResponse([
            'page' => $paginator->getCurrentPageNumber(),
            'perPage' => $paginator->getItemCountPerPage(),
            'totalPages' => $paginator->count(),
            'totalItems' => $paginator->getTotalItemCount(),
            'data' => JsonHelper::paginatorJsonDecode($paginator->getCurrentItems()),
        ]);
    }

}
