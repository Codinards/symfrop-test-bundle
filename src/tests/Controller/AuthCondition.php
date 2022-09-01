<?php

namespace Njeaner\Symfrop\Tests\Controller;

use Njeaner\Symfrop\Core\Annotation\Route;
use Njeaner\Symfrop\Core\Annotation\RouteAction;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;
use Njeaner\Symfrop\Entity\Contract\UserInterface;

class AuthCondition
{
    static $index = 0;

    #[Route('', name: 'action1', title: 'action1', actionCondition: [AuthCondition::class, 'condition1'], conditionOption: Route::CONDITION_OVERWRITE)]
    public function action1()
    {
    }

    #[RouteAction('action2', 'action2', CONSTANTS::ROLE_ALL, actionCondition: [AuthCondition::class, 'condition2'], conditionOption: RouteAction::CONDITION_OVERWRITE)]
    public function action2()
    {
    }

    #[RouteAction('action3', 'action3', CONSTANTS::ROLE_ALL, actionCondition: [AuthCondition::class, 'condition2'], conditionOption: RouteAction::CONDITION_CHECK_ONE)]
    public function action3()
    {
    }

    #[RouteAction('action4', 'action4', CONSTANTS::ROLE_ALL, actionCondition: [AuthCondition::class, 'condition2'], conditionOption: RouteAction::CONDITION_AT_TIME)]
    public function action4()
    {
    }

    public function condition1()
    {
        return true;
    }

    public function condition2(?UserInterface $auth)
    {
        return $auth?->getId() === 1;
    }
}
