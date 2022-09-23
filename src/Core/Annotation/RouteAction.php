<?php

namespace Njeaner\Symfrop\Core\Annotation;


use Attribute;
use Closure;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Njeaner\Symfrop\Core\Service\Config;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class RouteAction implements RouteActionInterface
{
    use RouteActionTrait;

    const CONDITION_AT_TIME = 0;
    const CONDITION_CHECK_ONE = 1;
    const CONDITION_OVERWRITE = 2;

    public function __construct(
        protected string $name,
        protected ?string $title = null,
        protected null|string|array $target = [],
        protected bool $isUpdatable = true,
        protected bool $hasAuth = true,
        protected bool  $isIndex = false,
        protected bool $isUpdated = false,
        protected bool $updatedRole = false,
        private null|string|array $actionCondition = null,
        private ?int $conditionOption = null
    ) {
        $this->initialize($name, $title, $target, $actionCondition);
    }
}
