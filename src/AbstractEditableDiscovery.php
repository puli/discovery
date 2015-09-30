<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery;

use BadMethodCallException;
use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\Initializer\BindingInitializer;
use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\Api\Type\BindingNotAcceptedException;
use Webmozart\Assert\Assert;

/**
 * Base class for editable resource discoveries.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractEditableDiscovery implements EditableDiscovery
{
    /**
     * @var BindingInitializer[]
     */
    private $initializers;

    /**
     * @var BindingInitializer[][]
     */
    private $initializersByBindingClass = array();

    /**
     * Tests whether a binding matches the given parameter values.
     *
     * @param Binding $binding         The tested binding.
     * @param array   $parameterValues One or more parameter values indexed by
     *                                 parameter names.
     *
     * @return bool Returns `true` if the passed parameters match the values in
     *              the binding.
     */
    public static function testParameterValues(Binding $binding, array $parameterValues)
    {
        foreach ($parameterValues as $parameterName => $parameterValue) {
            if ($parameterValue !== $binding->getParameterValue($parameterName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a new resource discovery.
     *
     * @param BindingInitializer[] $initializers The binding initializers to
     *                                           apply to newly created or
     *                                           unserialized bindings.
     */
    public function __construct(array $initializers = array())
    {
        $this->initializers = $initializers;
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindings($typeName = null, array $parameterValues = array())
    {
        Assert::nullOrStringNotEmpty($typeName, 'The type name must be a non-empty string. Got: %s');

        if (null === $typeName && count($parameterValues) > 0) {
            throw new BadMethodCallException('The type name must be passed when searching bindings by a parameter value.');
        }

        if (count($parameterValues) > 0) {
            $this->removeBindingsWithParameterValues($typeName, $parameterValues);

            return;
        }

        if (null !== $typeName) {
            $this->removeBindingsWithTypeName($typeName);

            return;
        }

        $this->removeAllBindings();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindings($typeName = null, array $parameterValues = array())
    {
        Assert::nullOrStringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (null === $typeName && count($parameterValues) > 0) {
            throw new BadMethodCallException('The type class must be passed when searching bindings by a parameter value.');
        }

        if (count($parameterValues) > 0) {
            return $this->hasBindingsWithParameterValues($typeName, $parameterValues);
        }

        if (null !== $typeName) {
            return $this->hasBindingsWithTypeName($typeName);
        }

        return $this->hasAnyBinding();
    }

    /**
     * Removes all bindings from the discovery.
     */
    abstract protected function removeAllBindings();

    /**
     * Removes all bindings bound to the given binding type.
     *
     * @param string $typeName The name of the binding type.
     */
    abstract protected function removeBindingsWithTypeName($typeName);

    /**
     * Removes all bindings bound to the given type with certain parameter values.
     *
     * Only bindings with exactly the given parameter values should be removed.
     * Parameters that are not passed in $parameterValues should be ignored.
     *
     * @param string $typeName        The name of the binding type.
     * @param array  $parameterValues The parameter values to match.
     */
    abstract protected function removeBindingsWithParameterValues($typeName, array $parameterValues);

    /**
     * Returns whether the discovery contains bindings.
     *
     * @return bool Returns `true` if the discovery has bindings and `false`
     *              otherwise.
     */
    abstract protected function hasAnyBinding();

    /**
     * Returns whether the discovery contains bindings for the given type.
     *
     * @param string $typeName The name of the binding type.
     *
     * @return bool Returns `true` if bindings bound to the given binding type
     *              are found and `false` otherwise.
     */
    abstract protected function hasBindingsWithTypeName($typeName);

    /**
     * Returns whether the discovery contains bindings for the given type and
     * parameter values.
     *
     * Parameters that are not passed in $parameterValues should be ignored.
     *
     * @param string $typeName        The name of the binding type.
     * @param array  $parameterValues The parameter values to match.
     *
     * @return bool Returns `true` if the discovery contains bindings bound to
     *              the given type and with the given parameter values.
     */
    abstract protected function hasBindingsWithParameterValues($typeName, array $parameterValues);

    /**
     * Initializes a binding.
     *
     * @param Binding $binding The binding to initialize.
     *
     * @throws BindingNotAcceptedException If the loaded type does not accept
     *                                     the binding.
     */
    protected function initializeBinding(Binding $binding)
    {
        $binding->initialize($this->getBindingType($binding->getTypeName()));

        $bindingClass = get_class($binding);

        if (!isset($this->initializersByBindingClass[$bindingClass])) {
            $this->initializersByBindingClass[$bindingClass] = array();

            // Find out which initializers accept the binding
            foreach ($this->initializers as $initializer) {
                if ($initializer->acceptsBinding($bindingClass)) {
                    $this->initializersByBindingClass[$bindingClass][] = $initializer;
                }
            }
        }

        // Apply all initializers that we found
        foreach ($this->initializersByBindingClass[$bindingClass] as $initializer) {
            $initializer->initializeBinding($binding);
        }
    }

    /**
     * Initializes multiple bindings.
     *
     * @param Binding[] $bindings The bindings to initialize.
     */
    protected function initializeBindings(array $bindings)
    {
        foreach ($bindings as $binding) {
            $this->initializeBinding($binding);
        }
    }
}
