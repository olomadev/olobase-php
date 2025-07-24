<?php

declare(strict_types=1);

namespace Authentication\Authentication;

use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Olobase\Authentication\Helper\TokenEncryptHelper;
use Olobase\Authentication\Service\TokenService;
use Olobase\Authentication\Service\JwtEncoderInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TokenServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new TokenService(
            $container->get('config'),
            $container->get(StorageInterface::class),
            $container->get(TokenEncryptHelper::class),
            $container->get(JwtEncoderInterface::class),
        );
    }
}
