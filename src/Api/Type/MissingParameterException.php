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
 * Thrown when a binding parameter is missing.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class MissingParameterException extends RuntimeException
{
    /**
     * Creates a new exception for the given parameter name.
     *
     * @param string         $parameterName The name of the parameter that was
     *                                      missing.
     * @param string         $typeName      The name of the type that the
     *                                      parameter was searched on.
     * @param Exception|null $cause         The exception that caused this
     *                                      exception.
     *
     * @return static The created exception.
     */
    public static function forParameterName($parameterName, $typeName, Exception $cause = null)
    {
        return new static(sprintf(
            'The parameter "%s" is required for type "%s".',
            $parameterName,
            $typeName
        ), 0, $cause);
    }
}
