<?php

namespace Njeaner\Symfrop\Tests\Controller;

use Njeaner\Symfrop\Core\Annotation\RouteAction;

class MethodAttributeRepeated
{
    #[RouteAction('no_has_auth', 'no_has_auth', hasAuth: false)]
    #[RouteAction('no_has_auth', 'no_has_auth', hasAuth: false)]
    public function index()
    {
    }
}
