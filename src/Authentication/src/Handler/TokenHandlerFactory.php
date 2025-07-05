<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Common\Helper\ErrorWrapperInterface;
use Authentication\InputFilter\TokenFilter;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class TokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter = $pluginManager->get(TokenFilter::class);

        return new TokenHandler(
            $container->get('config'),
            $container->get(AuthenticationInterface::class),
            $inputFilter,
            $container->get(ErrorWrapperInterface::class)
        );
    }
}