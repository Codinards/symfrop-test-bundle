<?php

namespace Njeaner\Symfrop\Events;

use Njeaner\Symfrop\Core\Annotation\RouteAction;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
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
