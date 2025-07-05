<?php

declare(strict_types=1);

namespace Users\Handler;

use Users\Model\UserModelInterface;
use Users\Filter\PasswordSaveFilter;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdatePasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModelInterface $userModel,        
        private PasswordSaveFilter $filter,
        private Error $error,
    ) 
    {
    }

    /**
     * @OA\Put(
     *   path="/users/updatePassword/{userId}",
     *   tags={"Users"},
     *   summary="Update user passwors",
     *   operationId="users_updatePassword",
     *
     *   @OA\Parameter(
     *       name="userId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update user",
     *     @OA\JsonContent(ref="#/components/schemas/UserPasswordSave"),
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
            $this->userModel->updatePasswordById(
                $this->filter->getValue('id'),
                $this->filter->getValue('password')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response); 
    }
}
