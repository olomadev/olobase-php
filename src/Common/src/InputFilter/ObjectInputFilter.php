<?php

namespace Common\InputFilter;

use Laminas\InputFilter\OptionalInputFilter;
use Laminas\InputFilter\Exception\InvalidArgumentException;

/**
 * InputFilter which only checks the containing Inputs object data sets
 */
class ObjectInputFilter extends OptionalInputFilter
{
    /**
     * @var array
     */
    protected array $objectMessages = [];

    /**
     * {@inheritdoc}
     */
    public function getMessages(): array
    {
        $messages = [];

        foreach ($this->getInvalidInput() as $name => $input) {
            $messages[$name] = $input->getMessages();
        }

        return !empty($this->objectMessages) ? array_merge($messages, $this->objectMessages) : $messages;
    }

    /**
     * Set data to use when validating and filtering
     *
     * @param iterable|array $data
     * @throws InvalidArgumentException
     * @return self
     */
    public function setData($data): self
    {
        if (!is_iterable($data)) {
            throw new InvalidArgumentException("Data must be an iterable.");
        }
        //
        // ['id'] => null
        // fixes empty "id" data ...
        $content = implode("", array_map('strval', (array) $data));

        return parent::setData(!empty($content) ? $data : []);
    }
}
