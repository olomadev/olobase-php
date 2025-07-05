<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authorization\Schema\PermissionSave;
use Authorization\Filter\Permissions\SaveFilter;
use Olobase\Mezzio\Authorization\PermissionModelInterface;
use Olobase\Mezzio\DataManagerInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CopyHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionModelInterface $permissionModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
    }
    
    /**
     * @OA\Post(
     *   path="/authorization/permissions/copy/{permId}",
     *   tags={"Authorization Permissions"},
     *   summary="Copy a permission",
     *   operationId="authorizationPermissions_copy",
     *   
     *   @OA\RequestBody(
     *     description="Create a new permission",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionSave"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $permId = $request->getAttribute("permId");
        $post = $this->permissionModel->copy($permId);
        $this->filter->setInputData($post);
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getSaveData(PermissionSave::class, 'permissions');
            $this->permissionModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);     
    }
}
