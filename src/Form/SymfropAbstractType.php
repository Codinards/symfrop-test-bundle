<?php

namespace Njeaner\Symfrop\Form;


use Njeaner\Symfrop\Core\Service\Config;
use Njeaner\Symfrop\Twig\SymfropTwigExtension;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class SymfropAbstractType extends SymfropBaseType
{
    protected Config $config;

    public function __construct()
    {
        parent::__construct(SymfropTwigExtension::getInstance());
        $this->config = Config::getInstance();
    }
}
