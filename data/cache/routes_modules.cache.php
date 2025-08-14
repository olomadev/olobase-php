<?php

return array (
  '_lastModified' => 1754810361,
  'data' => 
  array (
    0 => 
    array (
      'path' => '/api/modules/create',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Modules\\Handler\\CreateHandler',
      ),
      'methods' => 
      array (
        0 => 'POST',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Modules',
        ),
      ),
    ),
  ),
);
