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

use RuntimeException;
use Webmozart\Assert\Assert;

/**
 * A parameter that can be set during binding.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingParameter
{
    /**
     * Flag: The parameter is optional.
     */
    const OPTIONAL = 0;

    /**
     * Flag: The parameter is required.
     */
    const REQUIRED = 1;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * Creates a new parameter.
     *
     * @param string $name         The parameter name.
     * @param int    $flags        A bitwise combination of the flag constants
     *                             in this class.
     * @param mixed  $defaultValue The parameter's default value.
     */
    public function __construct($name, $flags = self::OPTIONAL, $defaultValue = null)
    {
        Assert::stringNotEmpty($name, 'The parameter name must be a non-empty string. Got: %s');
        Assert::startsWithLetter($name, 'The parameter name must start with a letter. Got: %s');
        Assert::nullOrInteger($flags, 'The parameter "$flags" must be an integer or null. Got: %s');

        if (($flags & self::REQUIRED) && null !== $defaultValue) {
            throw new RuntimeException('Required parameters must not have default values.');
        }

        $this->name = $name;
        $this->flags = $flags;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the name of the parameter.
     *
     * @return string The parameter name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the flags passed to the constructor.
     *
     * @return int A bitwise combination of the flag constants in this class.
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Returns the default value of the parameter.
     *
     * @return mixed The default value.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns whether the parameter is required.
     *
     * @return bool Returns `true` if the parameter is required and `false`
     *              otherwise.
     */
    public function isRequired()
    {
        return (bool) ($this->flags & self::REQUIRED);
    }
}
