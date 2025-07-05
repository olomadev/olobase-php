<?php

declare(strict_types=1);

namespace Authentication\Middleware;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Firebase\JWT\ExpiredException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * This signal is controlled by the frontend, do not change the value.
     */
    protected const EXPIRE_SIGNAL = 'Token Expired';

    protected $authentication;
    protected $config;
    protected $translator;

    public function __construct(
        array $config,
        AuthenticationInterface $authentication
    )
    {
        $this->authentication = $authentication;
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {        
        try {
            $user = $this->authentication->authenticate($request);

            if (null !== $user) {
                return $handler->handle($request->withAttribute(UserInterface::class, $user));
            }
        } catch (ExpiredException $e) { // 401 Unauthorized client response
            return new JsonResponse(
                [
                    'data' => [
                        'error' => Self::EXPIRE_SIGNAL]
                    ],
                    401,
                    [
                        'Token-Expired' => 1
                    ]
            );
        }
        return $this->authentication->unauthorizedResponse($request);
    }
}
