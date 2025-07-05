<?php

namespace Common\InputFilter;

use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\InputFilter\InputFilterInterface as LaminasInputFilterInterface;

interface InputFilterInterface extends LaminasInputFilterInterface
{
    /**
     * Returns the input data
     *
     * @return array
     */
    public function getData(): array;
}
