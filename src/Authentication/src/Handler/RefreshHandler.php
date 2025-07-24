<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Exception;
use Olobase\Attribute\Route;
use Firebase\JWT\ExpiredException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Common\Helper\ValidationErrorFormatterInterface as Error;
use Olobase\Authentication\JwtEncoderInterface as JwtEncoder;
use Mezzio\Authentication\AuthenticationInterface;
use OpenApi\Attributes as OA;

#[Route(
    path: '/api/auth/refresh',
    methods: ['POST'],
)]
class RefreshHandler implements RequestHandlerInterface
{
    private $config;
    protected const LOGOUT_SIGNAL = 'Logout';

    public function __construct(
        array $config,
        private AuthenticationInterface $authentication,
        private Error $error
    ) {
        $this->config = $config;
    }
    
    #[OA\Post(
        path: '/auth/refresh',
        tags: ['Authentication'],
        summary: 'Refresh the token',
        operationId: 'auth_refresh',
        requestBody: new OA\RequestBody(
            description: 'Token refresh request',
            content: new OA\JsonContent(
                type: 'object',
                required: ['token'],
                properties: [
                    new OA\Property(
                        property: 'token',
                        type: 'string'
                    )
                ]
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
                response: 401,
                description: 'Unauthorized Response: token is expired'
            )
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $post = $request->getParsedBody();

        if (empty($post['token'])) {
            return new JsonResponse(
                [
                    'data' => ['error' => self::LOGOUT_SIGNAL] // no token, exited
                ],
                401
            );
        }
        $token = $this->tokenModel->getTokenEncrypt()->decrypt($post['token']);
        if (!$token) {
            return new JsonResponse(
                [
                    'data' => ['error' => self::LOGOUT_SIGNAL] // token is invalid
                ],
                401
            );
        }
        try {
            $this->tokenModel->decode($token); // token verification
        } catch (ExpiredException $e) {
            
            list($header, $payload, $signature) = explode(".", $token);
            $payload = json_decode(base64_decode($payload), true);

            if (json_last_error() != JSON_ERROR_NONE) {
                return new JsonResponse(
                    [
                        'data' => ['error' => "Invalid token"]
                    ],
                    401
                );
            }
            $data = $this->authentication->getTokenService()->refresh($request, $payload); // token renewal process
            if (false == $data) {
                return new JsonResponse(
                    [
                        'data' => ['error' => self::LOGOUT_SIGNAL] // token could not be refreshed
                    ],
                    401
                );
            }
            $details = $data['data']['details']; // new token and user information
            return new JsonResponse(
                [
                    'data' => [
                        'token' => $data['token'],
                        'user'  => [
                            'id' => $details['id'],
                            'fullname' => $details['fullname'],
                            'email' => $details['email'],
                            'permissions' => $data['data']['roles'],
                        ],
                        'expiresAt' => $data['expiresAt'],
                    ],
                ]
            );
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'data' => ['error' => $e->getMessage()]
                ],
                401
            );
        }

        return new JsonResponse(
            [
                'data' => ['info' => "Token not expired to refresh"]
            ]
        );
    }
}
