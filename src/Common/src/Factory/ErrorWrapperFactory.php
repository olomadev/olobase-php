<?php

declare(strict_types=1);

namespace Common\Factory;

use Common\Helper\ErrorWrapper;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ErrorWrapperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new ErrorWrapper;
    }
}