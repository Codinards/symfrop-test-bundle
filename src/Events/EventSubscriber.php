<?php

namespace Njeaner\Symfrop\Events;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
 */
class EventSubscriber  implements EventSubscriberInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            RouteActionCreated::class => 'onRouteActionCreated',
        ];
    }


    public function onRouteActionCreated(RouteActionCreated $event)
    {
        dd($event);
    }
}
