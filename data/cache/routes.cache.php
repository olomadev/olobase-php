<?php

return array (
  '_lastModified' => 1756192855,
  'data' => 
  array (
    0 => 
    array (
      'path' => '/api/authorization/roles/findAll',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Authorization\\Handler\\Roles\\FindAllHandler',
      ),
      'methods' => 
      array (
        0 => 'GET',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    1 => 
    array (
      'path' => '/api/authorization/roles/delete/:id',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Authorization\\Handler\\Roles\\DeleteHandler',
      ),
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    2 => 
    array (
      'path' => '/api/authorization/roles/findById/:id',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Authorization\\Handler\\Roles\\FindByIdHandler',
      ),
      'methods' => 
      array (
        0 => 'GET',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    3 => 
    array (
      'path' => '/api/authorization/roles/update/:id',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Authorization\\Handler\\Roles\\UpdateHandler',
      ),
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    4 => 
    array (
      'path' => '/api/authorization/roles/create',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Authorization\\Handler\\Roles\\CreateHandler',
      ),
      'methods' => 
      array (
        0 => 'POST',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    5 => 
    array (
      'path' => '/api/authorization/permissions/findAll',
      'pipeline' => 
      array (
        0 => 'Authorization\\Handler\\Permissions\\FindAllHandler',
      ),
      'methods' => 
      array (
        0 => 'GET',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    6 => 
    array (
      'path' => '/api/authorization/permissions/delete/:id',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Modularity\\Middleware\\EntityMiddleware',
        3 => 'Authorization\\Handler\\Permissions\\DeleteHandler',
      ),
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    7 => 
    array (
      'path' => '/api/authorization/permissions/update/:id',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Authorization\\Handler\\Permissions\\UpdateHandler',
      ),
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    8 => 
    array (
      'path' => '/api/authorization/permissions/create',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Modularity\\Middleware\\EntityMiddleware',
        3 => 'Authorization\\Handler\\Permissions\\CreateHandler',
      ),
      'methods' => 
      array (
        0 => 'POST',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
    9 => 
    array (
      'path' => '/api/authorization/permissions/findByPaging',
      'pipeline' => 
      array (
        0 => 'Authentication\\Middleware\\JwtAuthenticationMiddleware',
        1 => 'Mezzio\\Authorization\\AuthorizationMiddleware',
        2 => 'Authorization\\Handler\\Permissions\\FindByPagingHandler',
      ),
      'methods' => 
      array (
        0 => 'GET',
      ),
      'options' => 
      array (
        'meta' => 
        array (
          'module' => 'Authorization',
        ),
      ),
    ),
  ),
);
