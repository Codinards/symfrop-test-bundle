<?php

namespace Njeaner\Symfrop\Tests\Controller;

use Njeaner\Symfrop\Core\Annotation\Route;
use Njeaner\Symfrop\Core\Annotation\RouteAction;

#[RouteAction('no_has_auth', 'no_has_auth', hasAuth: false)]
#[Route('no_has_auth', 'no_has_auth', hasAuth: false)]
class ClassAttributeRepeated
{
}
