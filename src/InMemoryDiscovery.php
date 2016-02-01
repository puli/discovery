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
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\DuplicateTypeException;
use Puli\Discovery\Api\Type\NoSuchTypeException;
use Webmozart\Assert\Assert;
use Webmozart\Expression\Expr;
use Webmozart\Expression\Expression;

/**
 * A discovery that holds the bindings in memory.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryDiscovery extends AbstractEditableDiscovery
{
    /**
     * @var BindingType[]
     */
    private $types = array();

    /**
     * @var Binding[][]
     */
    private $bindingsByTypeName = array();

    /**
     * {@inheritdoc}
     */
    public function addBindingType(BindingType $type)
    {
        if (isset($this->types[$type->getName()])) {
            throw DuplicateTypeException::forTypeName($type->getName());
        }

        $this->types[$type->getName()] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        unset($this->types[$typeName]);

        $this->removeBindingsWithTypeName($typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingTypes()
    {
        $this->types = array();
        $this->bindingsByTypeName = array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        return isset($this->types[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingType($typeName)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (!isset($this->types[$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        return $this->types[$typeName];
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingTypes()
    {
        return count($this->types) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingTypes()
    {
        return array_values($this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function addBinding(Binding $binding)
    {
        $typeName = $binding->getTypeName();

        if (isset($this->bindingsByTypeName[$typeName])) {
            foreach ($this->bindingsByTypeName[$typeName] as $other) {
                if ($binding->equals($other)) {
                    return;
                }
            }
        }

        $this->initializeBinding($binding);

        $this->bindingsByTypeName[$typeName][] = $binding;
    }

    /**
     * {@inheritdoc}
     */
    public function findBindings($typeName, Expression $expr = null)
    {
        Assert::stringNotEmpty($typeName, 'The type class must be a non-empty string. Got: %s');

        if (!isset($this->bindingsByTypeName[$typeName])) {
            return array();
        }

        $bindings = $this->bindingsByTypeName[$typeName];

        if (null !== $expr) {
            $bindings = Expr::filter($bindings, $expr);
        }

        return array_values($bindings);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings()
    {
        $bindings = array();

        foreach ($this->bindingsByTypeName as $bindingsOfType) {
            foreach ($bindingsOfType as $binding) {
                $bindings[] = $binding;
            }
        }

        return $bindings;
    }

    /**
     * {@inheritdoc}
     */
    protected function removeAllBindings()
    {
        $this->bindingsByTypeName = array();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsThatMatch(Expression $expr)
    {
        foreach ($this->bindingsByTypeName as $typeName => $bindings) {
            foreach ($bindings as $key => $binding) {
                if ($expr->evaluate($binding)) {
                    unset($this->bindingsByTypeName[$typeName][$key]);
                }
            }

            if (0 === count($this->bindingsByTypeName[$typeName])) {
                unset($this->bindingsByTypeName[$typeName]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsWithTypeName($typeName)
    {
        if (!isset($this->bindingsByTypeName[$typeName])) {
            return;
        }

        unset($this->bindingsByTypeName[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsWithTypeNameThatMatch($typeName, Expression $expr)
    {
        if (!isset($this->bindingsByTypeName[$typeName])) {
            return;
        }

        foreach ($this->bindingsByTypeName[$typeName] as $key => $binding) {
            if ($expr->evaluate($binding)) {
                unset($this->bindingsByTypeName[$typeName][$key]);
            }
        }

        if (0 === count($this->bindingsByTypeName[$typeName])) {
            unset($this->bindingsByTypeName[$typeName]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function hasAnyBinding()
    {
        return count($this->bindingsByTypeName) > 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasBindingsThatMatch(Expression $expr)
    {
        foreach ($this->bindingsByTypeName as $typeName => $bindings) {
            foreach ($bindings as $key => $binding) {
                if ($expr->evaluate($binding)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasBindingsWithTypeName($typeName)
    {
        return !empty($this->bindingsByTypeName[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    protected function hasBindingsWithTypeNameThatMatch($typeName, Expression $expr)
    {
        if (!isset($this->bindingsByTypeName[$typeName])) {
            return false;
        }

        foreach ($this->bindingsByTypeName[$typeName] as $binding) {
            if ($expr->evaluate($binding)) {
                return true;
            }
        }

        return false;
    }
}
