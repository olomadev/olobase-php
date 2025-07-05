<?php

declare(strict_types=1);

namespace Users\Handler;

use Users\Model\UserModelInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindAllByPagingHandler($container->get(UserModelInterface::class));
    }
}
