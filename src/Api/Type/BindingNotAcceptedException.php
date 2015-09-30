<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Api\Type;

use Exception;
use RuntimeException;

/**
 * Thrown when a binding type does not accept a binding.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingNotAcceptedException extends RuntimeException
{
    /**
     * Creates a new exception.
     *
     * @param string         $typeName     The name of the binding type.
     * @param string         $bindingClass The class name of the binding.
     * @param Exception|null $cause        The exception that caused this
     *                                     exception.
     *
     * @return static The created exception.
     */
    public static function forBindingClass($typeName, $bindingClass, Exception $cause = null)
    {
        return new static(sprintf(
            'The type "%s" does accept bindings of class "%s".',
            $typeName,
            $bindingClass
        ), 0, $cause);
    }
}
