<?php

declare(strict_types=1);

namespace Authentication\Factory;

use Authentication\MyAuthenticationAdapter;
use Psr\Container\ContainerInterface;
use Olobase\Util\RequestHelper;
use Olobase\Authentication\JwtAuth\JwtEncoderInterface;
use Olobase\Authentication\JwtAuth\TokenInterface;
use Olobase\Authentication\JwtAuth\JwtAuthentication;
use Olobase\Authorization\Contract\RoleModelInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
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

        $excludedFields = [ // sensitive data columns must not shown in auth response.
            'password'
        ];
        $authAdapter = new MyAuthenticationAdapter(  // Change it with your own adapter ..
            $container->get(Adapter::class),
            $config['authentication']['adapter']['options']['table'],
            $config['authentication']['adapter']['options']['identity_column'],
            $config['authentication']['adapter']['options']['credential_column'],
            $passwordValidation
        );
        return new JwtAuthentication(
            config: $config,
            authAdapter: $authAdapter,
            encoderService: $container->get(JwtEncoderInterface::class),
            tokenService: $container->get(TokenInterface::class),
            roleModel: $container->get(RoleModelInterface::class),
            user: $container->has(UserInterface::class) ? $container->get(UserInterface::class) : null,
            ipAddress: RequestHelper::getRealUserIp(),
            excludedFields: $excludedFields
        );
    }
}
