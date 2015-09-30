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

use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * A type that a binding can be bound to.
 *
 * @since  1.0
 *
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
     * @var string[]
     */
    private $acceptedBindings = array();

    /**
     * Creates a new type.
     *
     * @param string             $name             The name of the type.
     * @param BindingParameter[] $parameters       The parameters that can be
     *                                             set for a binding.
     * @param string[]           $acceptedBindings The binding class names that
     *                                             can be bound to this type.
     */
    public function __construct($name, array $parameters = array(), array $acceptedBindings = array())
    {
        Assert::stringNotEmpty($name, 'The type name must be a non-empty string. Got: %s');
        Assert::allIsInstanceOf($parameters, 'Puli\Discovery\Api\Type\BindingParameter');

        foreach ($acceptedBindings as $acceptedBinding) {
            if (!class_exists($acceptedBinding) && !interface_exists($acceptedBinding)) {
                throw new InvalidArgumentException(sprintf(
                    'The binding class "%s" is neither a class nor an '.
                    'interface name. Is there a typo?',
                    $acceptedBinding
                ));
            }
        }

        $this->name = $name;
        $this->acceptedBindings = $acceptedBindings;

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
     * Returns whether the type has parameters.
     *
     * @return bool Returns `true` if the type has parameters and `false`
     *              otherwise.
     */
    public function hasParameters()
    {
        return count($this->parameters) > 0;
    }

    /**
     * Returns whether the type has any required parameters.
     *
     * @return bool Returns `true` if the type has at least one required
     *              parameter.
     */
    public function hasRequiredParameters()
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->isRequired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the type has any optional parameters.
     *
     * @return bool Returns `true` if the type has at least one optional
     *              parameter.
     */
    public function hasOptionalParameters()
    {
        foreach ($this->parameters as $parameter) {
            if (!$parameter->isRequired()) {
                return true;
            }
        }

        return false;
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

    /**
     * Returns the default values of the parameters.
     *
     * @return array The default values of the parameters.
     */
    public function getParameterValues()
    {
        $values = array();

        foreach ($this->parameters as $name => $parameter) {
            if (!$parameter->isRequired()) {
                $values[$name] = $parameter->getDefaultValue();
            }
        }

        return $values;
    }

    /**
     * Returns whether the type has parameters with default values.
     *
     * @return bool Returns `true` if at least one parameter has a default value
     *              and `false` otherwise.
     */
    public function hasParameterValues()
    {
        foreach ($this->parameters as $name => $parameter) {
            if (!$parameter->isRequired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the default value of a parameter.
     *
     * @param string $name The parameter name.
     *
     * @return mixed The default value.
     *
     * @throws NoSuchParameterException If the parameter was not found.
     */
    public function getParameterValue($name)
    {
        return $this->getParameter($name)->getDefaultValue();
    }

    /**
     * Returns whether a parameter has a default value set.
     *
     * @param string $name The parameter name.
     *
     * @return bool Returns `true` if the parameter has a default value set and
     *              `false` otherwise.
     *
     * @throws NoSuchParameterException If the parameter was not found.
     */
    public function hasParameterValue($name)
    {
        return !$this->getParameter($name)->isRequired();
    }

    /**
     * Returns whether the type accepts a binding class.
     *
     * @param string $className The fully-qualified name of the binding class.
     *
     * @return bool Returns `true` if the binding class can be bound to this
     *              type and `false` otherwise.
     */
    public function acceptsBinding($className)
    {
        if (empty($this->acceptedBindings) || in_array($className, $this->acceptedBindings, true)) {
            return true;
        }

        foreach ($this->acceptedBindings as $acceptedBinding) {
            if (is_subclass_of($className, $acceptedBinding)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the binding class names that can be bound to this type.
     *
     * Returns an empty array if any binding can be bound to the type.
     *
     * @return string[] An array of class names or an empty array.
     */
    public function getAcceptedBindings()
    {
        return $this->acceptedBindings;
    }
}
