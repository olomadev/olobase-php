<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Exception;
use Common\Attribute\Route;
use Authentication\InputFilter\TokenFilter;
use Firebase\JWT\ExpiredException;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Common\Helper\ErrorWrapperInterface as Error;
use Mezzio\Authentication\AuthenticationInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/auth/token',
    methods: ['POST'],
)]
class TokenHandler implements RequestHandlerInterface
{
    private $config;
    private const EXPIRE_SIGNAL = 'Token Expired';

    public function __construct(
        array $config, 
        private AuthenticationInterface $authentication,
        private TokenFilter $filter,
        private Error $error
    ) {
        $this->config = $config;
    }

    #[OA\Post(
        path: '/auth/token',
        tags: ['Authentication'],
        summary: 'Authenticate the user',
        operationId: 'auth_token',
        requestBody: new OA\RequestBody(
            description: 'Login credentials',
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/TokenRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            ref: '#/components/schemas/UserObject'
                        ),
                        new OA\Property(
                            property: 'avatar',
                            type: 'object',
                            ref: '#/components/schemas/AvatarObject'
                        ),
                        new OA\Property(
                            property: 'expiresAt',
                            type: 'string',
                            format: 'date-time',
                            description: 'Expiration date of token'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns to validation errors'
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        if ($this->filter->isValid()) {
            try {
                $user = $this->authentication->createUser($request);
                if (null !== $user) {
                    $request = $request->withAttribute(UserInterface::class, $user);
                    $encoded = $this->authentication->getTokenService()->create($request);
                    $details = $user->getDetails();
                    $date = new \DateTime($encoded['expiresAt'], new \DateTimeZone('UTC'));

                    return new JsonResponse(
                        [
                            'data' => [
                                'token' => $encoded['token'],
                                'user'  => [
                                    'id' => $details['id'],
                                    'fullname' => $details['fullname'],
                                    'email' => $user->getIdentity(),
                                    'permissions' => $user->getRoles(),
                                ],
                                'avatar' => $details['avatar'],
                                'expiresAt' => $date->format('Y-m-d\TH:i:s.v\Z')                                
                            ]
                        ]
                    );
                }
            } catch (ExpiredException $e) {
                return new JsonResponse(
                    [
                        'data' => [
                            'error' => Self::EXPIRE_SIGNAL,
                            'message' => 'Your token has expired. Please login again.'
                        ]
                    ], 
                    401,
                    ['Token-Expired' => 1]
                );
            } catch (Exception $e) {
                return new JsonResponse(
                    [
                        'data' => ['error' => $e->getMessage()]
                    ], 
                    400
                );
            }
            return $this->authentication->unauthorizedResponse($request);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
    }
}
