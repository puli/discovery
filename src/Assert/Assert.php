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

use Assert\Assertion;

/**
 * Contains domain-specific assertions.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @method static void nullOrTypeName($value, $message = null, $propertyPath = null)
 * @method static void nullOrParameterName($value, $message = null, $propertyPath = null)
 * @method static void allTypeName($value, $message = null, $propertyPath = null)
 * @method static void allParameterName($value, $message = null, $propertyPath = null)
 */
class Assert extends Assertion
{
    public static function typeName($typeName)
    {
        Assert::string($typeName, 'The type name must be a string. Got: %2$s');
        Assert::notEmpty($typeName, 'The type name must not be empty.');
        Assert::true(ctype_alpha($typeName[0]), 'The type name must start with a letter.');
    }

    public static function parameterName($parameterName)
    {
        Assertion::string($parameterName, 'The parameter name must be a string. Got: %2$s');
        Assertion::notEmpty($parameterName, 'The parameter name must not be empty.');
        Assertion::true(ctype_alpha($parameterName[0]), 'The parameter name must start with a letter.');
    }

    private function __construct()
    {
    }
}
