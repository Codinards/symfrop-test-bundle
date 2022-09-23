<?php

namespace Njeaner\Symfrop\Entity\Contract;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
interface UserInterface extends SymfonyUserInterface
{

    /**
     * @return RoleInterface
     */
    public function getRole(): ?RoleInterface;

    /**
     * @param RoleInterface $role
     * @return UserInterface
     */
    public function setRole(RoleInterface $role): UserInterface;

    /**
     * @param ActionInterface $action
     * @return boolean
     */
    public function hasAction(ActionInterface $action): bool;

    /**
     * @return ActionInterface[]
     */
    public function getActions(): array;

    public function __toString(): string;
}
