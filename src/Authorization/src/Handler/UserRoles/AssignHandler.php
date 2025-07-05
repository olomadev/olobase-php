<?php

declare(strict_types=1);

namespace Authorization\Handler\UserRoles;

use Authorization\Model\UserRoleModelInterface;
use Authorization\Schema\UserRoleAssignment;
use Authorization\InputFilter\UserRoles\AssignRoleFilter;
use Olobase\Mezzio\DataManagerInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AssignHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRoleModelInterface $userRoleModel,
        private DataManagerInterface $dataManager,
        private AssignRoleFilter $filter,
        private Error $error,
    ) 
    {
    }
    
    /**
     * @OA\Put(
     *   path="/authorization/userRoles/assign",
     *   tags={"Authorization Roles"},
     *   summary="Assing a role for user",
     *   operationId="authorizationUserRoles_assign",
     *
     *   @OA\RequestBody(
     *     description="Assign a new user role",
     *     @OA\JsonContent(ref="#/components/schemas/UserRoleAssignment"),
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
            $data = $this->dataManager->getSaveData(UserRoleAssignment::class, 'userRoles');
            $userId = $data['userRoles']['userId'];
            $roleId = $data['userRoles']['roleId'];
            $this->userRoleModel->assignRole($userId, $roleId);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);     
    }
}
