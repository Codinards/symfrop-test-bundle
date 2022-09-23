<?php

namespace Njeaner\Symfrop\Tests\Controller;

use Njeaner\Symfrop\Core\Annotation\RouteAction;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class NoHasAuthController
{
    #[RouteAction('no_has_auth', 'no_has_auth', hasAuth: false)]
    public function noHasAuth()
    {
    }
}
