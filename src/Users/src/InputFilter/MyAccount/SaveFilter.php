<?php

declare(strict_types=1);

namespace Users\InputFilter\MyAccount;

use Common\InputFilter\InputFilter;
use Common\InputFilter\Filters\ToFile;
use Common\InputFilter\ObjectInputFilter;
use Common\Validator\BlobFileUpload;
use Laminas\Filter\StringTrim;
use Laminas\Validator\InArray;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Authentication\UserInterface;

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
            'name' => 'email',
            'required' => true,
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ],
                [
                    'name' => NoRecordExists::class,
                    'options' => [
                        'table'   => 'users',
                        'field'   => 'email',
                        'exclude' => [
                            'field' => 'id',
                            'value' => $data['userId'],
                        ],
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'firstname',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
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
            'filters' => [
                ['name' => StringTrim::class],
            ],
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

        // render & set data
        //
        $this->setData($data);
    }
}
