<?php

namespace Njeaner\Symfrop\Form\Validators;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class StringContainsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof StringContains) {
            throw new NotFoundHttpException(StringContains::class . ' expected in ' . __METHOD__ . ', given ' . get_class($constraint));
        }

        if ($value == null or $value === '') {
            $this->context->buildViolation('This value is required')->addViolation();
        } else {
            $search = $constraint->getSearch();
            $position = $constraint->getPosition();
            if ($position !== null) {
                if (stripos($value, $search) !== $position) {
                    $this->context->buildViolation(
                        $position === 0 ?
                            'This value must start with "' . $search . '" string' :
                            'This value must contains "' . $search . '" string at position "' . $position . '"'
                    )->addViolation();
                }
            } else {
                if (!str_contains($value, $search)) {
                    $this->context->buildViolation(
                        'This value must contains "' . $search . '" string'
                    )->addViolation();
                }
            }
        }
    }
}
