<?php

namespace Common\Router;

interface AttributeRouteProviderInterface
{
    public function registerRoutes(string $moduleDirectory): void;
}