<?php

namespace Njeaner\Symfrop\Core\Annotation;

use Closure;
use Njeaner\Symfrop\Core\Service\Config;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Njeaner\Symfrop\Entity\Contract\ActionInterface;

trait RouteActionTrait
{
    static $ALL = [];

    public function initialize(
        ?string $name,
        ?string $title,
        null|string|array $target,
        null|string|array $actionCondition,
    ) {
        if ($title === null) {
            $this->title = $name;
        }
        try {
            self::$ALL = array_keys(Config::getInstance()->getRoles());
        } catch (\Exception) {
            self::$ALL = CONSTANTS::ROLE_ALL;
        }
        $this->target = ($target !== null ? (is_string($target) ? [$target] : (empty($target) ? self::$ALL : $target)) : []);
        if (is_array($actionCondition)) {
            if ((count($actionCondition) !== 2) || (!is_string($actionCondition[0]) and !is_string($actionCondition[1]))) {
                throw new RouteActionException(__CLASS__ . '::$actionCondition array values must be an array of two string elements(["Classname", "CalledMethod"])');
            }
            if (!class_exists($actionCondition[0])) {
                throw new RouteActionException('The class "' . $actionCondition[0] . '" does not exist. (in ' . __CLASS__ . '::$actionCondition)');
            }
            if (!method_exists($actionCondition[0], $actionCondition[1])) {
                throw new RouteActionException('Method "' . $actionCondition[1] . '" does not exist in class "' . $actionCondition[0] . '". (in ' . __CLASS__ . '::$actionCondition)');
            }
        } elseif (is_string($actionCondition)) {
            if (!method_exists($actionCondition, '__invoke')) {
                throw new RouteActionException('The class "' . $actionCondition . '" is not an invokable class. (in ' . __CLASS__ . '::$actionCondition)');
            }
        }
        if ($this->actionCondition !== null and $this->conditionOption === null) {
            $this->conditionOption = self::CONDITION_AT_TIME;
        }
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string[]
     */
    public function getTarget(): ?array
    {
        return $this->target;
    }

    /**
     * @return bool
     */
    public function getIsUpdatable(): bool
    {
        return $this->isUpdatable;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @return boolean
     */
    public function getHasAuth(): bool
    {
        return $this->hasAuth;
    }

    /**
     * @return boolean
     */
    public function getIsIndex(): bool
    {
        return $this->isIndex;
    }

    /**
     * @return boolean
     */
    public function getIsUpdated(): bool
    {
        return $this->isUpdated;
    }

    public function updateAction(ActionInterface $action): ActionInterface
    {
        if ($this->name !== $action->getName()) $action->setName($this->name);
        if ($this->title !== $action->getTitle()) $action->setTitle($this->title);
        if ($this->isUpdatable !== $action->getIsUpdatable()) $action->setIsUpdatable($this->isUpdatable);
        if ($this->hasAuth !== $action->getHasAuth()) $action->setHasAuth($this->hasAuth);
        if ($this->isIndex !== $action->getIsIndex()) $action->setIsIndex($this->isIndex);
        if ($this->actionCondition !== $action->getCondition()) $action->setCondition($this->getActionCondition());
        if ($this->conditionOption !== $action->getConditionOption()) $action->setConditionOption($this->getConditionOption());
        return $action;
    }

    /**
     * Tell if the role permission has been updated
     *
     * @return boolean
     */
    public function getUpdatedRole(): bool
    {
        return $this->updatedRole;
    }

    public function getActionCondition(): null|string
    {
        if ($this->actionCondition instanceof Closure) {
            $refl = new \Opis\Closure\ReflectionClosure($this->actionCondition);
            return '$condition = ' . $refl->getCode() . ';';
        } elseif (is_array($this->actionCondition)) {
            return json_encode($this->actionCondition);
        }
        return $this->actionCondition;
    }

    public function setActionCondition(null|string|array $actionCondition): self
    {
        $this->actionCondition = $actionCondition;

        return $this;
    }

    public function getConditionOption(): ?int
    {
        return $this->conditionOption;
    }

    public function setConditionOption(?int $conditionOption): self
    {
        $this->conditionOption = $conditionOption;

        return $this;
    }
}
