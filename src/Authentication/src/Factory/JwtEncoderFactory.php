<?php

declare(strict_types=1);

namespace Authentication\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Olobase\Authentication\JwtAuth\JwtEncoder;
use Psr\Container\ContainerInterface;

class JwtEncoderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new JwtEncoder(config: $container->get('config'));
    }
}
