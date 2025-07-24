<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Common\Helper\ValidationErrorFormatterInterface
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class TokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);

        return new TokenHandler(
            $container->get('config'),
            $container->get(AuthenticationInterface::class),
            $pluginManager,
            $container->get(ValidationErrorFormatterInterface::class)
        );
    }
}