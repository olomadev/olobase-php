<?php

declare(strict_types=1);

namespace Authentication\Authentication;

use Common\Contracts\RoleModelInterface;
use Authentication\Model\TokenModelInterface;
use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface;
use Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;

class JwtAuthenticationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config') ?? [];
        if (! $container->has(UserInterface::class)) {
            throw new Exception\InvalidConfigException(
                'UserInterface factory service is missing for authentication'
            );
        }
        $passwordValidation = function ($hash, $password) {
            return password_verify($password, $hash);
        };

        $adapter = new AuthenticationAdapter(  // CallbackCheckAdapter
            $container->get(Adapter::class),
            $config['authentication']['adapter']['options']['table'],
            $config['authentication']['adapter']['options']['identity_column'],
            $config['authentication']['adapter']['options']['credential_column'],
            $passwordValidation
        );
        return new JwtAuthentication(
            $config,
            $adapter,
            $container->get(JwtEncoderInterface::class),
            $container->get(TokenModelInterface::class),
            $container->get(RoleModelInterface::class),
            $container->has(UserInterface::class) ? $container->get(UserInterface::class) : null
        );
    }
}
