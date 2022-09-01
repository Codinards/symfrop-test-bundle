<?php

namespace Njeaner\Symfrop\Entity\Contract;

use Doctrine\Common\Collections\Collection;

interface RoleInterface
{
    /**
     * @param ActionInterface $action
     * @return boolean
     */
    public function hasAction(ActionInterface $action): bool;

    /**
     * @return ActionInterface[]
     */
    public function getActions(): Collection;

    /**
     * @param ActionInterface $action
     * @return RoleInterface
     */
    public function addAction(ActionInterface $action): RoleInterface;

    public function removeAction(ActionInterface $action): RoleInterface;


    /**
     * Get the value of name
     */
    public function getName(): ?string;

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName(string $name): self;

    /**
     * @return boolean
     */
    public function getIsDeletable(): bool;

    /**
     * Set if the role is deletable
     *
     * @return self
     */
    public function setIsDeletable(bool $isDeletable): self;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Set the label of the role
     *
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Get all users that get this role
     *
     * @return Collection
     */
    public function getUsers(): Collection;

    public function __toString(): string;
}
