<?php

declare(strict_types=1);

namespace Users\Handler\MyAccount;

use Users\Model\UserModelInterface;
use Olobase\Mezzio\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindMeHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new FindMeHandler(
            $container->get(UserModelInterface::class),
            $container->get(DataManagerInterface::class)
        );
    }
}
