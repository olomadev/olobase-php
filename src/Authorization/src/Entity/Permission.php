<?php

declare(strict_types=1);

namespace Authorization\Entity;

use Olobase\Entity\AbstractEntity;

final class Permission extends AbstractEntity
{
    public function __construct(
        ?string $id = null,
        private readonly ?string $name = null,
        private readonly ?string $action = null,
        private readonly ?string $method = null,
    ) {
        parent::__construct($id);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAction(): array
    {
        return $this->action;
    }

    public function getMethod(): array
    {
        return $this->method;
    }
}
