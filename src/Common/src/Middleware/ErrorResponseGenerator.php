<?php

declare(strict_types=1);

namespace Common\Middleware;

use Throwable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mezzio\Cors\Configuration\ConfigurationInterface;

class ErrorResponseGenerator
{
    protected array $config;
    protected ContainerInterface $container;

    public function __construct(array $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;
    }

    public function __invoke(Throwable $e, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $e->getTrace();

        $trace = array_map(
            fn ($a) => isset($a['file']) && defined('PROJECT_ROOT') 
                ? array_merge($a, ['file' => str_replace(PROJECT_ROOT, '', $a['file'])]) 
                : $a,
            $data
        );

        $json = [
            'title'  => get_class($e),
            'type'   => 'https://httpstatus.es/400',
            'status' => 400,
            'file'   => defined('PROJECT_ROOT') ? str_replace(PROJECT_ROOT, '', $e->getFile()) : $e->getFile(),
            'line'   => $e->getLine(),
            'error'  => $e->getMessage(),
        ];

        if (getenv('APP_ENV') === 'local') {
            $json['trace'] = $trace;
        }

        $response = $response
            ->withHeader('Access-Control-Expose-Headers', 'Token-Expired')
            ->withHeader('Access-Control-Max-Age', '3600')
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);

        $response->getBody()->write(json_encode($json, JSON_THROW_ON_ERROR));

        // Error email notification (activated in production environment)
        // 
        if (getenv('APP_ENV') === 'prod') {
            $class = get_class($e);
            if (!str_contains($class, 'Laminas\Validator\Exception')) {

                // send error mail ...
            }
        }

        return $response;
    }
}
