<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Binding;

use RuntimeException;

/**
 * A parameter that can be set during binding.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingParameter
{
    /**
     * Mode: the parameter must be set
     */
    const REQUIRED = 1;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $mode;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * Creates a new parameter.
     *
     * @param string   $name         The parameter name.
     * @param int|null $mode         A bitwise combination of the parameter's
     *                               mode constants.
     * @param mixed    $defaultValue The parameter's default value.
     */
    public function __construct($name, $mode = null, $defaultValue = null)
    {
        if (($mode & self::REQUIRED) && null !== $defaultValue) {
            throw new RuntimeException('Required parameters must not have default values.');
        }

        $this->name = $name;
        $this->mode = (int) $mode;
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
     * Returns the parameter mode.
     *
     * @return int A bitwise combination of the parameter's mode constants.
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Returns whether the parameter must be set.
     *
     * This method is the inverse of {@link isOptional()}.
     *
     * @return bool Returns `true` if the parameter must be set.
     */
    public function isRequired()
    {
        return (bool) ($this->mode & self::REQUIRED);
    }

    /**
     * Returns whether the parameter is optional.
     *
     * This method is the inverse of {@link isRequired()}.
     *
     * @return bool Returns `true` if the parameter is optional.
     */
    public function isOptional()
    {
        return !$this->isRequired();
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
}
