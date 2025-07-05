<?php

declare(strict_types=1);

namespace Authorization\Handler\UserRoles;

use Exception;
use Common\Helper\RandomStringHelper;
use Authorization\Model\UserRoleModelInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandler implements RequestHandlerInterface
{
    public function __construct(private UserRoleModelInterface $userRoleModel)
    {
    }

    /**
     * @OA\Get(
     *   path="/authorization/userRoles/findAllByPaging/{roleId}",
     *   tags={"Authorization Roles"},
     *   summary="Find all roles by pagination",
     *   operationId="authorizationUserRoles_findAllByPaging",
     *
     *   @OA\Parameter(
     *       name="roleId",
     *       in="path",
     *       required=false,
     *       description="role Id",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="q",
     *       in="query",
     *       required=false,
     *       description="Search string",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_page",
     *       in="query",
     *       required=false,
     *       description="Page number",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_perPage",
     *       in="query",
     *       required=false,
     *       description="Per page",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_sort",
     *       in="query",
     *       required=false,
     *       description="Order items",
     *       @OA\Schema(
     *           type="array",
     *           @OA\Items()
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/UserRolesFindAllByPaging"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $get['roleId'] = $request->getAttribute('roleId');

        if (empty($get['roleId']) || false == RandomStringHelper::isUid($get['roleId'])) {
            throw new Exception("Invalid role id given.");
        }
        $page = empty($get['_page']) ? 1 : (int)$get['_page'];
        $perPage = empty($get['_perPage']) ? 10 : (int)$get['_perPage'];

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->userRoleModel->findAllByPaging($get);

        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return new JsonResponse([
            'page' => $paginator->getCurrentPageNumber(),
            'perPage' => $paginator->getItemCountPerPage(),
            'totalPages' => $paginator->count(),
            'totalItems' => $paginator->getTotalItemCount(),
            'data' => (array)$paginator->getCurrentItems(),
        ]);
    }

}
