<?php

declare(strict_types=1);

namespace Authentication\Authentication;

use Psr\Container\ContainerInterface;
use Olobase\Authentication\Service\JwtEncoderService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class JwtEncoderServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new JwtEncoderService($container->get('config'));
    }
}
