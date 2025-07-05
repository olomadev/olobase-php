<?php

declare(strict_types=1);

namespace Authorization\InputFilter\UserRoles;

use Common\InputFilter\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Authorization\Validator\Db\UserAndRoleRecordExists;

class UnassignRoleFilter extends InputFilter
{
    public function __construct(
        private AdapterInterface $adapter,
        private InputFilterPluginManager $filter
    )
    {
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'userId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $this->add([
            'name' => 'roleId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => UserAndRoleRecordExists::class,
                    'options' => [
                        'table'   => 'userRoles',
                        'field'   => 'roleId',
                        'userId'  => $data['userId'],
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);

        $this->setData($data);
    }
}
