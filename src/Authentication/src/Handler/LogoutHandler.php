<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Exception;
use Firebase\JWT\ExpiredException;
use Laminas\Diactoros\Response\JsonResponse;
use Modularity\Attribute\Route;
use Modularity\Authentication\JwtAuth\TokenInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function base64_decode;
use function explode;
use function json_decode;
use function json_last_error;
use function preg_match;

use const JSON_ERROR_NONE;

#[Route(
    path: '/api/auth/logout',
    methods: ['GET'],
)]
class LogoutHandler implements RequestHandlerInterface
{
    public function __construct(
        private TokenInterface $token
    ) {
    }

    #[OA\Get(
        path: '/auth/logout',
        tags: ['Authentication'],
        summary: 'Logout the user',
        operationId: 'auth_logout',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $token      = null;
        $authHeader = $request->getHeader('Authorization');

        // token check
        if (! empty($authHeader) && preg_match("/Bearer\s+(.*)$/i", $authHeader[0], $matches)) {
            $token = $matches[1];
        }
        if (empty($token)) {
            return new JsonResponse(
                [
                    'data' => [
                        'error' => "Invalid token",
                    ],
                ],
                401
            );
        }
        $token = $this->token->getTokenEncryptHelper()->decrypt($token);  // decode token
        try {
            $data = $this->token->decode($token);
            if (! empty($data['data']->details->id)) {
                $this->tokenModel->kill(
                    $data['data']->details->id,
                    $data['data']->details->tokenId
                );
            }
        } catch (ExpiredException $e) {
            [$header, $payload, $signature] = explode(".", $token);
            $base64DecodedToken             = base64_decode($payload);
            $token                          = json_decode($base64DecodedToken, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                return new JsonResponse(
                    [
                        'data' => [
                            'error' => "Invalid token",
                        ],
                    ],
                    401
                );
            }
            if ($token) { // terminate user with expired token
                $this->token->revokeToken(
                    $token['data']['details']['id'],
                    $token['data']['details']['tokenId']
                );
            }
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'data' => [
                        'error' => $e->getMessage(),
                    ],
                ],
                401
            );
        }
        return new JsonResponse([]);
    }
}
