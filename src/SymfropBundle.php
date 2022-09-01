<?php

namespace Njeaner\Symfrop;

use Njeaner\Symfrop\DependencyInjection\SymfropBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The symfrop Bundle
 *
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
 */
class SymfropBundle extends Bundle
{

    public function __construct()
    {
        $this->extension = new SymfropBundleExtension($this);
    }

    public function build(ContainerBuilder $container)
    {
    }

    public function getExtension(): SymfropBundleExtension
    {
        return $this->extension;
    }
}
