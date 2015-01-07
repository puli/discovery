<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Api\Validation;

use Puli\Discovery\Api\Binding\BindingType;

/**
 * Validates parameter values against the constraints defined by a binding type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ParameterValidator
{
    /**
     * Returns whether the given parameter values are valid.
     *
     * @param array       $parameters The parameter values to validate.
     * @param BindingType $type       The type to validate the values for.
     *
     * @return ConstraintViolation[] The found violations. If no violations were
     *                               found, an empty array is returned.
     */
    public function validate(array $parameters, BindingType $type);

}
