<?php

namespace Njeaner\Symfrop;

use Njeaner\Symfrop\DependencyInjection\SymfropBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The symfrop Bundle
 *
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class SymfropBundle extends Bundle
{

    public function __construct()
    {
        $this->extension = new SymfropBundleExtension($this);
        $this->getNamespace();
    }

    public function getExtension(): SymfropBundleExtension
    {
        return $this->extension;
    }
}
