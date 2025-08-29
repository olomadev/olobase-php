<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Authentication\Dto\TokenDataDto;
use Authentication\Dto\TokenMetaDto;
use Authentication\Dto\TokenRequestDto;
use Authentication\Dto\TokenResponseDto;
use Exception;
use Firebase\JWT\ExpiredException;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Modularity\Attribute\Route;
use Modularity\Filter\AttributeInputFilterCollector;
use Modularity\Validation\ErrorFormatterInterface;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Route(
    path: '/api/auth/token',
    methods: ['POST'],
)]
class TokenHandler implements RequestHandlerInterface
{
    private const EXPIRE_SIGNAL = 'Token Expired';

    public function __construct(
        private InputFilterPluginManager $filterManager,
        private AuthenticationInterface $authentication,
        private ErrorFormatterInterface $errorFormatter
    ) {
    }

    #[OA\Post(
        path: '/auth/token',
        tags: ['Authentication'],
        summary: 'Authenticate the user',
        operationId: 'auth_token',
        requestBody: new OA\RequestBody(
            description: 'Login credentials',
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TokenRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, returns to validation errors'
            ),
        ]
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dto       = new TokenRequestDto();
        $collector = new AttributeInputFilterCollector($this->filterManager);
        $filter    = $collector->fromObject($dto, $request->getParsedBody());

        if ($filter->isValid()) {
            try {
                $user = $this->authentication->authenticateWithCredentials($request);

                if ($user !== null) {
                    $request    = $request->withAttribute(UserInterface::class, $user);
                    $tokenClass = $this->authentication->getToken();
                    $tokenData  = $tokenClass->generateToken($request);
                    $data       = $tokenData['data'];

                    $dto = new TokenResponseDto(
                        token: $tokenData['token'],
                        data: new TokenDataDto(
                            roles: $data['roles'] ?? [],
                            details: $data['details'],
                            meta: new TokenMetaDto(
                                tokenId: $data['meta']['tokenId'],
                                ipAddress: $data['meta']['ipAddress'],
                                deviceKey: $data['meta']['deviceKey'],
                                expiresAt: $data['meta']['expiresAt']
                            ),
                            extra: $tokenData['extra'],
                        )
                    );
                    return new JsonResponse($dto);
                }
            } catch (ExpiredException $e) {
                return new JsonResponse(
                    [
                        'data' => [
                            'error'   => self::EXPIRE_SIGNAL,
                            'message' => 'Your token has expired. Please login again.',
                        ],
                    ],
                    401,
                    ['Token-Expired' => 1]
                );
            } catch (Exception $e) {
                return new JsonResponse(
                    [
                        'data' => ['error' => $e->getMessage()],
                    ],
                    400
                );
            }
            return $this->authentication->unauthorizedResponse($request);
        } else {
            return new JsonResponse($this->errorFormatter->format($filter), 400);
        }
    }
}
