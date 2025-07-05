<?php

declare(strict_types=1);

namespace Users\Handler\MyAccount;

use Users\Model\UserModelInterface;
use Users\Schema\MyAccount\MyAccountFindMe;
use Olobase\Mezzio\DataManagerInterface;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindMeHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModelInterface $userModel,
        private DataManagerInterface $dataManager
    ) 
    {
    }

    /**
     * @OA\Get(
     *   path="/users/myAccount/findMe",
     *   tags={"Users My Account"},
     *   summary="Find my account data",
     *   operationId="usersMyAccount_findOneById",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/MyAccountFindMe"),
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class); // get id from current token
        $userId = $user->getDetails()['id'];
        $row = $this->userModel->findOneById($userId);
        if ($row) {
            $data = $this->dataManager->getViewData(MyAccountFindMe::class, $row);
            return new JsonResponse($data);            
        }
        return new JsonResponse([], 404);
    }
}
