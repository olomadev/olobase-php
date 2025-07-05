<?php

declare(strict_types=1);

namespace Authentication\InputFilter;

use Common\InputFilter\InputFilter;
use Laminas\Filter\StringTrim;
use Laminas\Validator\EmailAddress;

/**
 * @OA\Schema(
 *   schema="TokenRequest",
 *   required={"username", "password"},
 *   @OA\Property(property="username", type="string", format="email"),
 *   @OA\Property(property="password", type="string")
 * )
 */
class TokenFilter extends InputFilter
{
    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'username',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
        ]);
        $this->setData($data);
    }
}
