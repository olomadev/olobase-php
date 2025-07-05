<?php

declare(strict_types=1);

namespace Authentication\Handler;

use Common\Helper\ErrorWrapperInterface;
use Authentication\Model\TokenModelInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RefreshHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new RefreshHandler(
            $container->get('config'), 
            $container->get(AuthenticationInterface::class), 
            $container->get(TokenModelInterface::class), 
            $container->get(ErrorWrapperInterface::class)
        );
    }
}
