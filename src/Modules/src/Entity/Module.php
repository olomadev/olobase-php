<?php

declare(strict_types=1);

namespace Modules\Entity;

use function preg_replace;
use function strtolower;

final class Module
{
    public function __construct(
        public readonly string $id,
        public string $name,
        public string $version,
        public bool $isActive,
    ) {
    }

    public function getUiName(): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->name));
    }
}
