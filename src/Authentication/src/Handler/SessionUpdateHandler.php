<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionUpdateHandler implements RequestHandlerInterface
{
    private $config;

    public function __construct(
        array $config,
        private StorageInterface $cache
    )
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * @OA\Get(
     *   path="/auth/session",
     *   tags={"Authentication"},
     *   summary="Update session with http requests",
     *   operationId="auth_session",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation"
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);
        if ($user) {
            $details = $user->getDetails();
            //
            // reset session ttl using cache 
            // 
            $userId = $details['id'];
            $tokenId = $details['tokenId'];
            $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60;
            $userHasSession = $this->cache->getItem(SESSION_KEY.$userId.":".$tokenId);
            if ($userHasSession) {
                // do not change the order of this code otherwise the user will be logged out quickly
                $this->cache->getOptions()->setTtl($configSessionTTL);
                $this->cache->setItem(SESSION_KEY.$userId.":".$tokenId, $configSessionTTL);
            }
            return new TextResponse("ok", 200);
        }
        return new TextResponse("logout", 200);
    }
}
