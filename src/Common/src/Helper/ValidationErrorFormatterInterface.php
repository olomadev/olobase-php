<?php

declare(strict_types=1);

namespace Common\Helper;

use Laminas\InputFilter\InputFilterInterface;

interface ValidationErrorFormatterInterface
{
    public function format(InputFilterInterface $filter): array;
}
