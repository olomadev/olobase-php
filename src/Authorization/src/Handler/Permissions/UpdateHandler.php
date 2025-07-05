<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Authorization\Schema\PermissionSave;
use Authorization\InputFilter\Permissions\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
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
     * @OA\Put(
     *   path="/authorization/permissions/update/{permId}",
     *   tags={"Authorization Permissions"},
     *   summary="Update a permission",
     *   operationId="authorizationPermissions_update",
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
        $this->filter->setInputData($request->getParsedBody());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getSaveData(PermissionSave::class, 'permissions');
            $this->permissionModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}
