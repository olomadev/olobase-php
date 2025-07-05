<?php

declare(strict_types=1);

namespace Authorization\Handler\Permissions;

use Common\Helper\JsonHelper;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;

class FindAllByPagingHandler implements RequestHandlerInterface
{
    public function __construct(private PermissionModelInterface $permissionModel)
    {
    }

    /**
     * @OA\Get(
     *   path="/authorization/permissions/findAllByPaging",
     *   tags={"Authorization Permissions"},
     *   summary="Find all permissions",
     *   operationId="authorizationPermissions_findAllByPaging",
     *
     *   @OA\Parameter(
     *       name="$filters",
     *       in="query",
     *       required=false,
     *       description="Search string",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionsFindAllByPaging"),
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
        $page = empty($get['_page']) ? 1 : (int)$get['_page'];
        $perPage = empty($get['_perPage']) ? 5 : (int)$get['_perPage'];

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->permissionModel->findAllByPaging($get);

        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return new JsonResponse([
            'page' => $paginator->getCurrentPageNumber(),
            'perPage' => $paginator->getItemCountPerPage(),
            'totalPages' => $paginator->count(),
            'totalItems' => $paginator->getTotalItemCount(),
            'data' => JsonHelper::paginatorJsonDecode($paginator->getCurrentItems()),
        ]);
    }

}
