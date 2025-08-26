<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Exception;
use Firebase\JWT\ExpiredException;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Modularity\Attribute\Route;
use Modularity\Validation\ValidationErrorFormatterInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function base64_decode;
use function explode;
use function json_decode;
use function json_last_error;

use const JSON_ERROR_NONE;

#[Route(
    path: '/api/auth/refresh',
    methods: ['POST'],
)]
class RefreshHandler implements RequestHandlerInterface
{
    protected const LOGOUT_SIGNAL = 'Logout';

    public function __construct(
        private AuthenticationInterface $authentication,
        private ValidationErrorFormatterInterface $errorFormatter
    ) {
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
                    ),
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
                            property: 'expiresAt',
                            type: 'string',
                            format: 'date-time',
                            description: 'Expiration date of token'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized Response: token is expired'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $post = $request->getParsedBody();

        if (empty($post['token'])) {
            return new JsonResponse(
                [
                    'data' => ['error' => self::LOGOUT_SIGNAL], // no token, exited
                ],
                401
            );
        }
        $tokenClass = $this->authentication->getToken();
        $token      = $tokenClass->getTokenEncryptHelper()->decrypt($post['token']);
        if (! $token) {
            return new JsonResponse(
                [
                    'data' => ['error' => self::LOGOUT_SIGNAL], // token is invalid
                ],
                401
            );
        }
        try {
            $tokenClass->decodeToken($token); // token verification
        } catch (ExpiredException $e) {
            [$header, $payload, $signature] = explode(".", $token);
            $payload                        = json_decode(base64_decode($payload), true);

            if (json_last_error() != JSON_ERROR_NONE) {
                return new JsonResponse(
                    [
                        'data' => ['error' => "Invalid token"],
                    ],
                    401
                );
            }
            $tokenResponse = $tokenClass->refreshToken($request, $payload); // token renewal process
            if (false == $tokenResponse) {
                return new JsonResponse(
                    [
                        'data' => ['error' => self::LOGOUT_SIGNAL], // token could not be refreshed
                    ],
                    401
                );
            }
            return new JsonResponse($tokenResponse);
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'data' => ['error' => $e->getMessage()],
                ],
                401
            );
        }

        return new JsonResponse(
            [
                'data' => ['info' => "Token not expired to refresh"],
            ]
        );
    }
}
