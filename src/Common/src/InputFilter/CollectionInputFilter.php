<?php

namespace Common\InputFilter;

use Laminas\InputFilter\CollectionInputFilter as LaminasCollectionInputFilter;
use Laminas\InputFilter\InputFilter;

class CollectionInputFilter extends LaminasCollectionInputFilter
{
    protected $isRequired = false; // Varsayılan olarak false

    /**
     * Get the input filter used when looping the data
     *
     * @return InputFilter
     */
    public function getInputFilter()
    {
        if (null === $this->inputFilter) {
            $this->setInputFilter(new InputFilter());
        }

        return $this->inputFilter;
    }

    /**
     * {@inheritdoc}
     * @param mixed $context Ignored, but present to retain signature compatibility.
     */
    public function isValid($context = null)
    {
        $this->collectionMessages = [];
        $inputFilter = $this->getInputFilter();
        $valid = true;

        // If required and the collection is empty, an error is thrown.
        if ($this->isRequired && $this->getCount() < 1) {
            $this->collectionMessages[] = $this->prepareRequiredValidationFailureMessage();
            return false;
        }

        //If there is no data, clear it and return it as invalid.
        if (empty($this->data)) {
            $this->clearValues();
            $this->clearRawValues();
            return false;
        }

        // Validate data in loop
        foreach ($this->data as $key => $data) {
            $inputFilter->setData($data);

            if (isset($this->validationGroup[$key])) {
                $inputFilter->setValidationGroup($this->validationGroup[$key]);
            }

            if ($inputFilter->isValid($context)) {
                $this->validInputs[$key] = $inputFilter->getValidInput();
            } else {
                $valid = false;
                $this->collectionMessages[$key] = $inputFilter->getMessages();
                $this->invalidInputs[$key] = $inputFilter->getInvalidInput();
            }

            $this->collectionValues[$key] = $inputFilter->getValues();
            $this->collectionRawValues[$key] = $inputFilter->getRawValues();
        }

        return $valid;
    }
}
