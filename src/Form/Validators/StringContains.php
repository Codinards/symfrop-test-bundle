<?php

namespace Njeaner\Symfrop\Form\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class StringContains extends Constraint
{
    public function __construct(private string $search, private ?int $position = null)
    {
    }

    /**
     * Get the value of search
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * Get the value of position
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }
}
