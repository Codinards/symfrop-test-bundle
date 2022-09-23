<?php

namespace Njeaner\Symfrop\Core\Annotation;

use Njeaner\Symfrop\Entity\Contract\ActionInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
interface RouteActionInterface
{
    public function getName(): ?string;

    public function getTitle(): ?string;

    public function getTarget(): ?array;

    public function getIsUpdatable(): bool;

    public function getModule(): string;

    public function getHasAuth(): bool;

    public function getIsIndex(): bool;

    public function getIsUpdated(): bool;

    public function updateAction(ActionInterface $action): ActionInterface;

    public function getUpdatedRole(): bool;

    public function getActionCondition(): null|string;

    public function setActionCondition(null|string|array $actionCondition): self;

    public function getConditionOption(): ?int;

    public function setConditionOption(?int $conditionOption): self;
}
