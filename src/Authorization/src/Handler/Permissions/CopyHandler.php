<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authorization\Dto\PermissionCreateDto;
use Authorization\Schema\PermissionSave;
use Olobase\Attribute\Route;
use Olobase\Mapper\InputSchemaMapper;
use Olobase\Authorization\PermissionModelInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/authorization/permissions/copy/:id',
    methods: ['POST'],
)]
class CopyHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionModelInterface $permissionModel,
        private InputFilterPluginManager $filterPluginManager,
        private Error $error,
    ) 
    {
    }
    
    #[OA\Post(
        path: '/authorization/permissions/copy/{permId}',
        tags: ['Authorization Permissions'],
        summary: 'Copy a permission',
        operationId: 'authorizationPermissions_copy',
        requestBody: new OA\RequestBody(
            description: 'Create a new permission',
            content: new OA\JsonContent(ref: '#/components/schemas/PermissionSave'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation'
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns to validation errors'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $permId = $request->getAttribute("permId");
        $post = $this->permissionModel->buildCreateDataFromPermission($permId);
        $dto = new PermissionCreateDto();
        $collector = new AttributeInputFilterCollector($this->filterPluginManager);
        $filter = $collector->fromObject($dto, $post);
        
        $data = array();
        $response = array();
        if ($filter->isValid()) {
            $mapper = new InputSchemaMapper();
            $data = $mapper->map($filter, PermissionSave::class);
            $this->permissionModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);     
    }
}
