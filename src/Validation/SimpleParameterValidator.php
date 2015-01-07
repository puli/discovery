<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Validation;

use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Api\Validation\ConstraintViolation;
use Puli\Discovery\Api\Validation\ParameterValidator;

/**
 * A simple parameter validator implementation.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SimpleParameterValidator implements ParameterValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $parameters, BindingType $type)
    {
        $violations = array();

        foreach ($parameters as $name => $value) {
            if (!$type->hasParameter($name)) {
                $violations[] = new ConstraintViolation(
                    ConstraintViolation::NO_SUCH_PARAMETER,
                    $value,
                    $type->getName(),
                    $name
                );
            }
        }

        foreach ($type->getParameters() as $parameter) {
            if (!isset($parameters[$parameter->getName()])) {
                if ($parameter->isRequired()) {
                    $violations[] = new ConstraintViolation(
                        ConstraintViolation::MISSING_PARAMETER,
                        $parameters,
                        $type->getName(),
                        $parameter->getName()
                    );
                }
            }
        }

        return $violations;
    }

}
