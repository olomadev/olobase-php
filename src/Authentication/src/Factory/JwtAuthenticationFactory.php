<?php

declare(strict_types=1);

namespace Authentication\Factory;

use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use Olobase\Authentication\JwtAuth\JwtAuthentication;
use Olobase\Authentication\JwtAuth\JwtEncoderInterface;
use Olobase\Authentication\JwtAuth\TokenInterface;
use Olobase\Authorization\RoleRepositoryInterface;
use Psr\Container\ContainerInterface;

use function password_verify;

class JwtAuthenticationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
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
        $adapterClass       = $config['authentication']['adapter']['class'];

        $authAdapter = new $adapterClass(
            $container->get(Adapter::class),
            $config['authentication']['adapter']['options']['table'],
            $config['authentication']['adapter']['options']['identity_column'],
            $config['authentication']['adapter']['options']['credential_column'],
            $passwordValidation
        );

        return new JwtAuthentication(
            config: $config,
            authAdapter: $authAdapter,
            jwtEncoder: $container->get(JwtEncoderInterface::class),
            token: $container->get(TokenInterface::class),
            roleRepository: $container->get(RoleRepositoryInterface::class),
            userFactory: $container->get(UserInterface::class)
        );
    }
}
