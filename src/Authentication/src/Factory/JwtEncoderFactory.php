<?php

declare(strict_types=1);

namespace Authentication\Factory;

use Psr\Container\ContainerInterface;
use Olobase\Authentication\Service\JwtEncoder;
use Laminas\ServiceManager\Factory\FactoryInterface;

class JwtEncoderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new JwtEncoder(config: $container->get('config'));
    }
}
