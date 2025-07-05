<?php

declare(strict_types=1);

namespace Authorization\InputFilter\Permissions;

use Common\InputFilter\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;

class DeleteFilter extends InputFilter
{
    public function __construct(private AdapterInterface $adapter)
    {
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => RecordExists::class,
                    'options' => [
                        'table'   => 'permissions',
                        'field'   => 'id',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
       
        $this->setData($data);
    }
}
