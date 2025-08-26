<?php

declare(strict_types=1);

namespace Authentication\Factory;

use Laminas\Cache\Storage\StorageInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Modularity\Authentication\JwtAuth\JwtEncoderInterface;
use Modularity\Authentication\JwtAuth\SessionAwareToken;
use Modularity\Authentication\JwtAuth\StatelessToken;
use Modularity\Authentication\Util\TokenEncryptHelper;
use Psr\Container\ContainerInterface;

class TokenFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config      = $container->get('config');
        $tokenConfig = $config['authentication']['token'] ?? [];
        $tokenClass  = empty($tokenConfig['enable_session_ttl']) ? StatelessToken::class : SessionAwareToken::class;

        return new $tokenClass(
            config: $config,
            cache: $container->get(StorageInterface::class),
            tokenEncrypt: $container->get(TokenEncryptHelper::class),
            jwtEncoder: $container->get(JwtEncoderInterface::class),
        );
    }
}
