<?php

declare(strict_types=1);

namespace Users\InputFilter;

use Laminas\Db\Adapter\AdapterInterface;
use Common\InputFilter\InputFilter;
use Common\InputFilter\Filters\ToFile;
use Common\InputFilter\ObjectInputFilter;
use Common\Validator\BlobFileUpload;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authentication\UserInterface;
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
                        'table'   => 'users',
                        'field'   => 'id',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'firstname',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 120,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'lastname',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 120,
                    ],
                ],
            ],
        ]);

        $emailOptions = $method == 'PUT' ? [
            'table'   => 'users',
            'field'   => 'email',
            'adapter' => $this->adapter,
            'exclude' => ['field' => 'email', 'value' => $data['email']],
        ] : [
            'table'   => 'users',
            'field'   => 'email',
            'adapter' => $this->adapter
        ];
        $this->add([
            'name' => 'email',
            'required' => false,
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ],
                [
                    'name' => NoRecordExists::class,
                    'options' => $emailOptions,
                ]
            ],
        ]);
        $this->add([
            'name' => 'password',
            'required' => $method == 'POST' ? true : false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 8,
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
            ],
        ]);
        $this->add([
            'name' => 'isEmailActivated',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'image',
            'required' => false,
            'filters' => [
                ['name' => ToFile::class],
            ],
            'validators' => [
                [
                    'name' => BlobFileUpload::class,
                    'options' => [
                        'operation' => $method == 'POST' ? 'create' : 'update',
                        'max_allowed_upload' => 2097152,  // 2 mega bytes
                        'mime_types' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                            'image/webp',
                        ],
                    ],
                ]
            ]
        ]);
        $this->add($objectFilter, 'avatar');

        $this->setData($data);
    }
}
