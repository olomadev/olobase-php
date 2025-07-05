<?php

declare(strict_types=1);

namespace Users\Handler\MyAccount;

use Users\Model\UserModelInterface;
use Users\InputFilter\MyAccount\PasswordChangeFilter;
use Mezzio\Authentication\UserInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdatePasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModelInterface $userModel,        
        private PasswordChangeFilter $filter,
        private Error $error,
    ) 
    {
        $this->userModel = $userModel;
        $this->error = $error;
        $this->filter = $filter;
    }
    
    /**
     * @OA\Put(
     *   path="/users/myAccount/updatePassword",
     *   tags={"Users My Account"},
     *   summary="Update password",
     *   operationId="usersMyAccount_updatePassword",
     *
     *   @OA\RequestBody(
     *     description="Update Password",
     *     @OA\JsonContent(ref="#/components/schemas/MyAccountUpdatePassword"),
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
        $user = $request->getAttribute(UserInterface::class);
        $userId = $user->getDetails()['id'];
        $this->filter->setInputData($request->getParsedBody());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->userModel->updatePasswordById(
                $userId, 
                $this->filter->getValue('newPassword')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response); 
    }
}
