<?php

declare(strict_types=1);

namespace Modules\Handler;

use Modules\Model\ModuleModelInterface;
use Modules\Schema\ModuleSave;
use Modules\InputFilter\SaveFilter;
use Olobase\Mezzio\DataManagerInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private ModuleModelInterface $moduleModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
    }
    
    /**
     * @OA\Put(
     *   path="/modules/update/{moduleId}",
     *   tags={"Modules"},
     *   summary="Update module",
     *   operationId="modules_update",
     *
     *   @OA\Parameter(
     *       name="moduleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update role",
     *     @OA\JsonContent(ref="#/components/schemas/ModuleSave"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     @OA\JsonContent(ref="#/components/schemas/ModuleUpdateResponse"),
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
            $data = $this->dataManager->getSaveData(ModuleSave::class, 'modules');
            $module = $data['modules'];
            $moduleId = $this->filter->getValue('id');
            if ($module['name'] == 'Modules') {
                return new JsonResponse(
                    [
                        'data' => [
                            'info' => 'The core module `Modules` cannot be modified',
                        ]
                    ],
                    400
                );
            }
            $oldRow = $this->moduleModel->findOneById($moduleId);
            $this->moduleModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        $response['data']['oldRecord'] = $oldRow;
        return new JsonResponse($response);   
    }
}
