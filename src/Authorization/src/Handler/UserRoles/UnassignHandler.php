<?php

declare(strict_types=1);

namespace Authorization\Handler\UserRoles;

use Authorization\Model\UserRoleModelInterface;
use Authorization\InputFilter\UserRoles\UnassignRoleFilter;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UnassignHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserRoleModelInterface $userRoleModel,        
        private UnassignRoleFilter $filter,
        private Error $error,
    ) 
    {
    }
    
    /**
     * @OA\Put(
     *   path="/authorization/userRoles/unassign",
     *   tags={"Authorization Roles"},
     *   summary="Unassign role",
     *   operationId="authorizationUserRoles_unassign",
     *   
     *   @OA\RequestBody(
     *     description="Unassign a user role",
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
        if ($this->filter->isValid()) {
            $userId = $this->filter->getValue('userId');
            $roleId = $this->filter->getValue('roleId');
            $this->userRoleModel->unassignRole($userId, $roleId);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
