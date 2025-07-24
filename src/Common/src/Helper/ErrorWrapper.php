<?php

declare(strict_types=1);

namespace Common\Helper;

use Laminas\InputFilter\InputFilterInterface;

/**
 * @author Oloma <support@oloma.dev>
 *
 * Wrap error messages
 */
class ErrorWrapper implements ValidationErrorFormatterInterface
{
    public function format(InputFilterInterface $inputFilter): array
    {
        return $this->getMessages($inputFilter);
    }

    public function getMessages(InputFilterInterface $inputFilter, bool $multipleError = true): array
    {
        $response = [];

        foreach ($inputFilter->getInvalidInput() as $field => $input) {
            $getMessages = $input->getMessages();
            if (!$multipleError) {
                $errors = array_values($getMessages);
                if (!empty($errors[0])) {
                    $response['data']['error'] = $errors[0];
                    break;
                }
            }

            foreach ($getMessages as $key => $message) {
                if (is_array($message)) {
                    $arrayMessages = [];
                    foreach ($message as $k => $v) {
                        if (is_string($v)) {
                            $arrayMessages[$key][] = "$field: $v";
                        } elseif (is_array($v)) {
                            foreach (array_values($v) as $sv) {
                                $arrayMessages[$k][] = "$k: $sv";
                            }
                        }
                    }
                    $response['data']['error'][$field][] = $arrayMessages;
                } else {
                    $response['data']['error'][$field][] = $message;
                }
            }
        }

        return $response;
    }
}
