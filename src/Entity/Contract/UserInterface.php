<?php

namespace Njeaner\Symfrop\Entity\Contract;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

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
