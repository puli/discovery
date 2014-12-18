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

use Assert\Assertion;
use InvalidArgumentException;

/**
 * A type that a resource can be bound to.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var BindingParameter[]
     */
    private $parameters = array();

    /**
     * Creates a new type.
     *
     * @param string             $name       The name of the type.
     * @param BindingParameter[] $parameters The parameters that can be set
     *                                       during binding.
     */
    public function __construct($name, array $parameters = array())
    {
        Assertion::allIsInstanceOf($parameters, 'Puli\Discovery\Binding\BindingParameter');

        $this->name = $name;

        foreach ($parameters as $parameter) {
            $this->parameters[$parameter->getName()] = $parameter;
        }

        // Sort to facilitate comparison
        ksort($this->parameters);
    }

    /**
     * Returns the type's name.
     *
     * @return string The name of the type.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the parameters.
     *
     * @return BindingParameter[] The type parameters.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $name The parameter name.
     *
     * @return BindingParameter The parameter.
     *
     * @throws NoSuchParameterException If the parameter was not found.
     */
    public function getParameter($name)
    {
        if (!isset($this->parameters[$name])) {
            throw new NoSuchParameterException(sprintf(
                'The parameter "%s" does not exist on type "%s".',
                $name,
                $this->name
            ));
        }

        return $this->parameters[$name];
    }

    /**
     * Returns whether a parameter exists.
     *
     * @param string $name The parameter name.
     *
     * @return bool Returns `true` if a parameter with that name exists.
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }
}
