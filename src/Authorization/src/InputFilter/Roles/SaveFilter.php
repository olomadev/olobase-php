<?php

declare(strict_types=1);

namespace Authorization\InputFilter\Roles;

use Laminas\Filter\ToInt;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Common\InputFilter\InputFilter;
use Common\InputFilter\CollectionInputFilter;
use Psr\Http\Message\ServerRequestInterface;

class SaveFilter extends InputFilter
{
    public function __construct(
        private AdapterInterface $adapter,
        private InputFilterPluginManager $filter,
        private ServerRequestInterface $request
    )
    {
    }

    public function setInputData(array $data)
    {
        $method = $this->request->getMethod();

        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => $method == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'roles',
                        'field'   => 'id',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'key',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 60,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'name',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'level',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        // Role Permissions Input filter
        //
        $collection = $this->filter->get(CollectionInputFilter::class);
        $inputFilter = $this->filter->get(InputFilter::class);
        $inputFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $collection->setInputFilter($inputFilter);
        $this->add($collection, 'rolePermissions');

        $this->setData($data);
    }
}
