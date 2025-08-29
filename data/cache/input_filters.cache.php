<?php

return array (
  'Authentication\\Dto\\TokenRequestDto' => 
  array (
    '_lastModified' => 1756285455,
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
);
