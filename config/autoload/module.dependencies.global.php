<?php

return array(
  'module_dependencies' =>
  array(
    'Common' =>
    array(
      0 => 'Common\Helper\JsonHelper',
      1 => 'Common\Helper\RandomStringHelper',
      2 => 'Common\Helper\RequestHelper',
      3 => 'Common\Helper\ErrorWrapperInterface',
      4 => 'Common\Middleware\CorsMiddleware',
      5 => 'Common\Middleware\ErrorResponseGenerator',
      6 => 'Common\Middleware\JsonBodyParserMiddleware',
    ),
    'Authentication' =>
    array(
      0 => 'Authentication\Middleware\JwtAuthenticationMiddleware',
    ),
    'Authorization' =>
    array(
      0 => 'Mezzio\Authorization\AuthorizationInterface',
      1 => 'Mezzio\Authorization\AuthorizationMiddleware',
    ),
  ),
);
