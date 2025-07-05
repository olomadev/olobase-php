<?php

declare(strict_types=1);

namespace Common\Helper;

use Laminas\InputFilter\InputFilterInterface;

/**
 * @author Oloma <support@oloma.dev>
 *
 * Wrap error messages
 */
class ErrorWrapper implements ErrorWrapperInterface
{
    /**
     * Create input filter error messages
     * 
     * @param  InputFilterInterface $inputFilter   Laminas input filter
     * @param  boolean              $multipleError show first error or all
     * @return array
     */
    public function getMessages(InputFilterInterface $inputFilter, $multipleError = true) : array
    {
        $response = array();
        foreach ($inputFilter->getInvalidInput() as $field => $input) {
            $getMessages = $input->getMessages();
            if (false == $multipleError) {
                $errors = array_values($getMessages);
                if (! empty($errors[0])) {
                    $response['data']['error'] = $errors[0];
                    break;
                }
            }
            foreach ($getMessages as $key => $message) {
                if (is_array($message)) {
                    $arrayMessages = array();
                    foreach ($message as $k => $v) {
                        if (is_string($v)) {
                            $arrayMessages[$key][] = $field.': '.$v;
                        } else if (is_array($v)) {
                            $subValues = array_values($v);
                            foreach ($subValues as $sField => $sv) {
                                $arrayMessages[$k][] = $k.': '.$sv;
                            }
                        }
                    }
                    $response['data']['error'][$field][] = $arrayMessages;
                } else {
                    $response['data']['error'][$field][] = $field.': '.$message;
                }
            }
        }
        return $response;
    }

    /**
     * Handle upload error messages
     *
     * @return string
     */
    public function getUploadError(int $code) : string
    {
        $message = "";
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;

            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;

            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;

            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;

            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;

            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;
            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }


}