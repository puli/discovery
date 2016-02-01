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
use Puli\Discovery\Api\Binding\Binding;
use Serializable;
use Webmozart\Assert\Assert;

/**
 * A type that a binding can be bound to.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class BindingType implements Serializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $acceptedBindingClass;

    /**
     * @var BindingParameter[]
     */
    private $parameters = array();

    /**
     * Creates a new type.
     *
     * @param string             $name         The name of the type.
     * @param string             $bindingClass The class name of the accepted
     *                                         bindings.
     * @param BindingParameter[] $parameters   The parameters that can be set
     *                                         for a binding.
     */
    public function __construct($name, $bindingClass, array $parameters = array())
    {
        Assert::stringNotEmpty($name, 'The type name must be a non-empty string. Got: %s');
        Assert::allIsInstanceOf($parameters, 'Puli\Discovery\Api\Type\BindingParameter');

        if (!class_exists($bindingClass) && !interface_exists($bindingClass)) {
            throw new InvalidArgumentException(sprintf(
                'The binding class "%s" is neither a class nor an '.
                'interface name. Is there a typo?',
                $bindingClass
            ));
        }

        $this->name = $name;
        $this->acceptedBindingClass = $bindingClass;

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
     * @param Binding|string $binding The binding or the fully-qualified name of
     *                                the binding class.
     *
     * @return bool Returns `true` if the binding can be bound to this type and
     *              `false` otherwise.
     */
    public function acceptsBinding($binding)
    {
        return $binding instanceof $this->acceptedBindingClass
            || $binding === $this->acceptedBindingClass
            || is_subclass_of($binding, $this->acceptedBindingClass);
    }

    /**
     * Returns the binding class name that can be bound to this type.
     *
     * @return string The accepted binding class name.
     */
    public function getAcceptedBindingClass()
    {
        return $this->acceptedBindingClass;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $data = array();

        $this->preSerialize($data);

        return serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->postUnserialize($data);
    }

    protected function preSerialize(array &$data)
    {
        $data[] = $this->name;
        $data[] = $this->acceptedBindingClass;

        foreach ($this->parameters as $parameter) {
            $data[] = $parameter->getName();
            $data[] = $parameter->getFlags();
            $data[] = $parameter->getDefaultValue();
        }
    }

    protected function postUnserialize(array &$data)
    {
        while (count($data) > 2) {
            $defaultValue = array_pop($data);
            $flags = array_pop($data);
            $name = array_pop($data);

            $this->parameters[$name] = new BindingParameter($name, $flags, $defaultValue);
        }

        $this->acceptedBindingClass = array_pop($data);
        $this->name = array_pop($data);
    }
}
