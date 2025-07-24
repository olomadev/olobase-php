<?php

namespace Common\InputFilter;

use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Authentication\UserInterface;
use Laminas\InputFilter\InputFilterInterface as LaminasInputFilterInterface;
use Laminas\InputFilter\InputFilter as LaminasInputFilter;

class InputFilter extends LaminasInputFilter implements InputFilterInterface
{
    /**
     * Returns the input data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }

    /**
     * Add an input to the input filter
     *
     * @param array|Traversable|InputInterface|InputFilterInterface $input
     * @param null|string $name
     * @return self
     */
    public function add($input, $name = null): self
    {
        parent::add($input, $name);
        return $this;
    }
}
