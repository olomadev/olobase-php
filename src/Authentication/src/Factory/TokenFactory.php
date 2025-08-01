<?php

declare(strict_types=1);

namespace Authentication\Factory;

use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Olobase\Authentication\Util\TokenEncryptHelper;
use Olobase\Authentication\JwtAuth\Token;
use Olobase\Authentication\JwtAuth\JwtEncoderInterface;
use Olobase\Authentication\JwtAuth\SessionAwareToken;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TokenServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');
        $tokenConfig = $config['token'] ?? [];
        $tokenClass = empty($tokenConfig['enable_session_ttl']) ? StatelessToken::class : SessionAwareToken::class;

        return new $tokenClass(
            config: $config,
            cache: $container->get(StorageInterface::class),
            tokenEncrypt: $container->get(TokenEncryptHelper::class),
            encoder: $container->get(JwtEncoderInterface::class),
        );
    }
}
