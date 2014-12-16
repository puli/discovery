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
        $this->name = $name;

        foreach ($parameters as $parameter) {
            if (!$parameter instanceof BindingParameter) {
                throw new \InvalidArgumentException(sprintf(
                    'The parameters must be of type BindingParameter. Got: %s',
                    is_object($parameter) ? get_class($parameter) : gettype($parameter)
                ));
            }

            $this->parameters[$parameter->getName()] = $parameter;
        }
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
     * @return array The type parameters.
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
