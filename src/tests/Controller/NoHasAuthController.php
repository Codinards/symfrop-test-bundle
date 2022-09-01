<?php

namespace Njeaner\Symfrop\Tests\Controller;

use Njeaner\Symfrop\Core\Annotation\RouteAction;

class NoHasAuthController
{
    #[RouteAction('no_has_auth', 'no_has_auth', hasAuth: false)]
    public function noHasAuth()
    {
    }
}
