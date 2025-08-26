<?php

declare(strict_types=1);

namespace Authorization\Entity;

use Modularity\Entity\AbstractEntity;

final class Permission extends AbstractEntity
{
    public function __construct(
        ?string $id = null,
        private ?string $module = null,
        private ?string $name = null,
        private ?string $action = null,
        private ?string $route = null,
        private ?string $method = null,
    ) {
        parent::__construct($id);
    }

    public function getModule(): ?string
    {
        return (string) $this->module;
    }

    public function getName(): ?string
    {
        return (string) $this->name;
    }

    public function getAction(): string
    {
        return (string) $this->action;
    }

    public function getRoute(): ?string
    {
        return (string) $this->route;
    }

    public function getMethod(): string
    {
        return (string) $this->method;
    }
}
