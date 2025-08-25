<?php

return array (
  'Authorization\\Dto\\PermissionDeleteDto' => 
  array (
    '_lastModified' => 1755177512,
    'data' => 
    array (
      0 => 
      array (
        'type' => 'input',
        'name' => 'id',
        'required' => true,
        'filters' => 
        array (
        ),
        'validators' => 
        array (
          0 => 
          array (
            'name' => 'Laminas\\Validator\\Uuid',
          ),
          1 => 
          array (
            'name' => 'Laminas\\Validator\\Db\\RecordExists',
            'options' => 
            array (
              'table' => 'permissions',
              'field' => 'id',
            ),
          ),
        ),
      ),
    ),
  ),
  'Authentication\\Dto\\TokenRequestDto' => 
  array (
    '_lastModified' => 1754472650,
    'data' => 
    array (
      0 => 
      array (
        'type' => 'input',
        'name' => 'username',
        'required' => true,
        'filters' => 
        array (
          0 => 
          array (
            'name' => 'Laminas\\Filter\\StringTrim',
          ),
        ),
        'validators' => 
        array (
          0 => 
          array (
            'name' => 'Laminas\\Validator\\EmailAddress',
            'options' => 
            array (
              'useMxCheck' => false,
            ),
          ),
        ),
      ),
      1 => 
      array (
        'type' => 'input',
        'name' => 'password',
        'required' => true,
        'filters' => 
        array (
          0 => 
          array (
            'name' => 'Laminas\\Filter\\StringTrim',
          ),
        ),
        'validators' => 
        array (
        ),
      ),
    ),
  ),
  'Authorization\\Dto\\PermissionCreateDto' => 
  array (
    '_lastModified' => 1755852156,
    'data' => 
    array (
      0 => 
      array (
        'type' => 'input',
        'name' => 'name',
        'required' => true,
        'filters' => 
        array (
        ),
        'validators' => 
        array (
        ),
      ),
      1 => 
      array (
        'type' => 'object',
        'name' => 'action',
        'fields' => 
        array (
          0 => 
          array (
            'name' => 'id',
            'required' => true,
            'validators' => 
            array (
              0 => 
              array (
                'name' => 'Laminas\\Validator\\InArray',
                'options' => 
                array (
                  'haystack' => 
                  array (
                    0 => 'create',
                    1 => 'delete',
                    2 => 'edit',
                    3 => 'list',
                    4 => 'show',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      2 => 
      array (
        'type' => 'object',
        'name' => 'method',
        'fields' => 
        array (
          0 => 
          array (
            'name' => 'id',
            'required' => true,
            'validators' => 
            array (
              0 => 
              array (
                'name' => 'Laminas\\Validator\\InArray',
                'options' => 
                array (
                  'haystack' => 
                  array (
                    0 => 'GET',
                    1 => 'POST',
                    2 => 'PUT',
                    3 => 'DELETE',
                    4 => 'PATCH',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);
