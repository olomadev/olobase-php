<?php

namespace Swagger;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    security: [['bearerAuth' => []]]
)]
#[OA\Info(
    title: 'Example API',
    version: '1.0'
)]
#[OA\Server(
    url: 'https://example.com/api',
    description: 'Docs'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class OpenApiSpec
{
}
