<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Assert;

/**
 * Domain-specific assertions.
 *
 * @method static void nullOrTypeName($value)
 * @method static void nullOrParameterName($value)
 * @method static void allTypeName($values)
 * @method static void allParameterName($values)
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Assert extends \Puli\Repository\Assert\Assert
{
    public static function typeName($value)
    {
        self::string($value, 'The type name must be a string. Got: %s');
        self::notEmpty($value, 'The type name must not be empty.');
        self::true(ctype_alpha($value[0]), 'The type name must start with a letter.');
    }

    public static function parameterName($value)
    {
        self::string($value, 'The parameter name must be a string. Got: %s');
        self::notEmpty($value, 'The parameter name must not be empty.');
        self::true(ctype_alpha($value[0]), 'The parameter name must start with a letter.');
    }

    private function __construct()
    {
    }
}
