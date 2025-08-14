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
);
