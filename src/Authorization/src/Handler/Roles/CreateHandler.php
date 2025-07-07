<?php

declare(strict_types=1);

namespace Authorization\Handler\Roles;

use Olobase\Mezzio\Authorization\RoleModelInterface;
use Authorization\Schema\RoleSave;
use Authorization\InputFilter\Roles\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoleModelInterface $roleModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
    }
    
    /**
     * @OA\Post(
     *   path="/authorization/roles/create",
     *   tags={"Authorization Roles"},
     *   summary="Create a new role",
     *   operationId="authorizationRoles_create",
     *
     *   @OA\RequestBody(
     *     description="Create a new role",
     *     @OA\JsonContent(ref="#/components/schemas/RoleSave"),
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
            $data = $this->dataManager->getSaveData(RoleSave::class, 'roles');
            $this->roleModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);     
    }
}
