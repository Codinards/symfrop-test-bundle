<?php

namespace Njeaner\Symfrop\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * The symfrop route loader
 *
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
 */
class RouteLoader extends Loader
{
    public function load($resource, ?string $type = null): mixed
    {
        $routes = new RouteCollection();
        $resource = '@SymfropBundle/Resources/config/routes.yaml';
        $type = 'yaml';

        $importedRoutes = $this->import($resource, $type);
        $routes->addCollection($importedRoutes);
        return $routes;
    }

    public function supports($resource, ?string $type = null): bool
    {
        return (bool) null;
    }
}
