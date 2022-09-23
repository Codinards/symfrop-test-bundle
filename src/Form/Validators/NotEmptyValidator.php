<?php

namespace Njeaner\Symfrop\Form\Validators;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class NotEmptyValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotEmpty) {
            throw new NotFoundHttpException(NotEmpty::class . ' expected in ' . __METHOD__ . ', given ' . get_class($constraint));
        }

        if (!is_array($value) && !($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
            $this->context->buildViolation(
                'This value must be an array or an instance of iterable'
            )->addViolation();
        }

        if ((is_array($value) && empty($value) || $value->count() === 0)) {
            $this->context->buildViolation(
                'This value can not be empty. Please choices some options'
            )->addViolation();
        }
    }
}
