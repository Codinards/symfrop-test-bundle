<?php

namespace Njeaner\Symfrop\Events;

use Njeaner\Symfrop\Core\Annotation\RouteAction;

class RouteActionCreated
{
    public function __construct(private RouteAction $routeAction)
    {
    }

    public function getEntity(): RouteAction
    {
        return $this->routeAction;
    }
}
