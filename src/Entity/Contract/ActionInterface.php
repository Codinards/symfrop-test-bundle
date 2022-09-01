<?php

namespace Njeaner\Symfrop\Entity\Contract;

use Doctrine\Common\Collections\Collection;

interface ActionInterface
{

    public function getRoles(): Collection;

    /**
     * Get the value of name
     *
     * @return  string
     */
    public function getName(): ?string;

    /**
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name): self;

    /**
     * Get the value of title
     *
     * @return  string
     */
    public function getTitle(): ?string;

    /**
     * Set the value of title
     *
     * @param  string  $title
     *
     * @return  self
     */
    public function setTitle(?string $title): self;

    /**
     * Get the value of isUpdatable
     *
     * @return  bool
     */
    public function getIsUpdatable(): bool;

    /**
     * Set the value of isUpdatable
     *
     * @param  bool  $isUpdatable
     *
     * @return  self
     */
    public function setIsUpdatable(bool $isUpdatable): self;

    /**
     * Get the value of hasAuth
     *
     * @return  bool
     */
    public function getHasAuth(): bool;

    /**
     * Set the value of hasAuth
     *
     * @param  bool  $hasAuth
     *
     * @return  self
     */
    public function setHasAuth(bool $hasAuth): self;

    /**
     * Get the value of isIndex
     *
     * @return  bool
     */
    public function getIsIndex(): bool;

    /**
     * Set the value of isIndex
     *
     * @param  boolean  $isIndex
     *
     * @return  self
     */
    public function setIsIndex(bool $isIndex): self;

    public function __toString(): string;

    /**
     * Get the condition to authorize or denied action permission
     *
     * @return string|null
     */
    public function getCondition(): ?string;

    /**
     * Set condition to authorize or denied action permission
     *
     * @param string|null $condition
     * @return self
     */
    public function setCondition(?string $condition): self;

    /**
     * Get condition option resolution: overwrite, before, after, at_time
     *
     * @return integer|null
     */
    public function getConditionOption(): ?int;

    public function setConditionOption(?int $conditionOption): self;
}
