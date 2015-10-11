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

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\Initializer\BindingInitializer;
use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\Api\Type\BindingNotAcceptedException;
use Webmozart\Assert\Assert;
use Webmozart\Expression\Expression;

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
    public function removeBindings($typeName = null, Expression $expr = null)
    {
        Assert::nullOrStringNotEmpty($typeName, 'The type name must be a non-empty string. Got: %s');

        if (null !== $typeName) {
            if (null !== $expr) {
                $this->removeBindingsWithTypeNameThatMatch($typeName, $expr);
            } else {
                $this->removeBindingsWithTypeName($typeName);
            }
        } elseif (null !== $expr) {
            $this->removeBindingsThatMatch($expr);
        } else {
            $this->removeAllBindings();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindings($typeName = null, Expression $expr = null)
    {
        Assert::nullOrStringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (null !== $typeName) {
            if (null !== $expr) {
                return $this->hasBindingsWithTypeNameThatMatch($typeName, $expr);
            }

            return $this->hasBindingsWithTypeName($typeName);
        }

        if (null !== $expr) {
            return $this->hasBindingsThatMatch($expr);
        }

        return $this->hasAnyBinding();
    }

    /**
     * Removes all bindings from the discovery.
     */
    abstract protected function removeAllBindings();

    /**
     * Removes all bindings from the discovery that match an expression.
     *
     * @param Expression $expr The expression to filter by.
     */
    abstract protected function removeBindingsThatMatch(Expression $expr);

    /**
     * Removes all bindings bound to the given binding type.
     *
     * @param string $typeName The name of the binding type.
     */
    abstract protected function removeBindingsWithTypeName($typeName);

    /**
     * Removes all bindings bound to the given binding type that match an expression.
     *
     * @param string     $typeName The name of the binding type.
     * @param Expression $expr     The expression to filter by.
     */
    abstract protected function removeBindingsWithTypeNameThatMatch($typeName, Expression $expr);

    /**
     * Returns whether the discovery contains bindings.
     *
     * @return bool Returns `true` if the discovery has bindings and `false`
     *              otherwise.
     */
    abstract protected function hasAnyBinding();

    /**
     * Returns whether the discovery contains bindings that match an expression.
     *
     * @param Expression $expr The expression to filter by.
     *
     * @return bool Returns `true` if the discovery has bindings and `false`
     *              otherwise.
     */
    abstract protected function hasBindingsThatMatch(Expression $expr);

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
     * Returns whether the discovery contains bindings for the given type that
     * match an expression.
     *
     * @param string     $typeName The name of the binding type.
     * @param Expression $expr     The expression to filter by.
     *
     * @return bool Returns `true` if bindings bound to the given binding type
     *              are found and `false` otherwise.
     */
    abstract protected function hasBindingsWithTypeNameThatMatch($typeName, Expression $expr);

    /**
     * Filters the bindings that match the given expression.
     *
     * @param Binding[]  $bindings The bindings to filter.
     * @param Expression $expr     The expression to evaluate for each binding.
     *
     * @return Binding[] The filtered bindings.
     */
    protected function filterBindings(array $bindings, Expression $expr)
    {
        return array_values(array_filter($bindings, array($expr, 'evaluate')));
    }

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
