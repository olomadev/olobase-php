<?php

declare(strict_types=1);

namespace Modules\InputFilter;

use Common\InputFilter\InputFilter;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Http\Message\ServerRequestInterface;

class SaveFilter extends InputFilter
{
    public function __construct(
        private AdapterInterface $adapter,
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
                        'table'   => 'modules',
                        'field'   => 'id',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        
        $this->add([
            'name' => 'name',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 40,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'version',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 16,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'isActive',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ]
        ]);

        // render & set data
        //
        $this->setData($data);
    }
}
