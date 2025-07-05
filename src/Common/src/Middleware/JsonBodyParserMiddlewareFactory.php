<?php

declare(strict_types=1);

namespace Common\Middleware;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class JsonBodyParserMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new JsonBodyParserMiddleware;
    }
}