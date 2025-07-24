<?php

declare(strict_types=1);

namespace Authentication\Authentication;

use Psr\Container\ContainerInterface;
use Olobase\Authentication\Service\JwtEncoder;
use Laminas\ServiceManager\Factory\FactoryInterface;

class JwtEncoderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new JwtEncoder($container->get('config'));
    }
}
