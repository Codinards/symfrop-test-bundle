<?php

namespace Njeaner\Symfrop\Events;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function onKernelController(ControllerEvent $event): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
