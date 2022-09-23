<?php

namespace Njeaner\Symfrop\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * The symfrop route loader
 * 
 * This loader is not used in this bundle
 *
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
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
