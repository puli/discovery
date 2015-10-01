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
use Puli\Discovery\Api\Binding\NoSuchBindingException;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\DuplicateTypeException;
use Puli\Discovery\Api\Type\NoSuchTypeException;
use Rhumsaa\Uuid\Uuid;
use Webmozart\Assert\Assert;
use Webmozart\Expression\Expression;

/**
 * A resource discovery that holds the bindings in memory.
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
     * @var Binding[]
     */
    private $bindings = array();

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
        $this->bindings = array();
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
        $uuidString = $binding->getUuid()->toString();

        if (isset($this->bindings[$uuidString])) {
            return;
        }

        $this->initializeBinding($binding);

        $this->bindings[$uuidString] = $binding;
        $this->bindingsByTypeName[$binding->getTypeName()][$uuidString] = $binding;
    }

    /**
     * {@inheritdoc}
     */
    public function removeBinding(Uuid $uuid)
    {
        $uuidString = $uuid->toString();

        if (!isset($this->bindings[$uuidString])) {
            return;
        }

        $binding = $this->bindings[$uuidString];

        unset($this->bindings[$uuidString]);
        unset($this->bindingsByTypeName[$binding->getTypeName()][$uuidString]);
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
            $bindings = $this->filterBindings($bindings, $expr);
        }

        return array_values($bindings);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings()
    {
        return array_values($this->bindings);
    }

    /**
     * {@inheritdoc}
     */
    public function hasBinding(Uuid $uuid)
    {
        return isset($this->bindings[$uuid->toString()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBinding(Uuid $uuid)
    {
        if (!isset($this->bindings[$uuid->toString()])) {
            throw NoSuchBindingException::forUuid($uuid);
        }

        return $this->bindings[$uuid->toString()];
    }

    /**
     * {@inheritdoc}
     */
    protected function removeAllBindings()
    {
        $this->bindings = array();
        $this->bindingsByTypeName = array();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBindingsThatMatch(Expression $expr)
    {
        foreach ($this->bindings as $uuidString => $binding) {
            if ($expr->evaluate($binding)) {
                unset($this->bindings[$uuidString]);
                unset($this->bindingsByTypeName[$binding->getTypeName()][$uuidString]);
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

        foreach ($this->bindingsByTypeName[$typeName] as $binding) {
            unset($this->bindings[$binding->getUuid()->toString()]);
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

        foreach ($this->bindingsByTypeName[$typeName] as $uuidString => $binding) {
            if ($expr->evaluate($binding)) {
                unset($this->bindings[$uuidString]);
                unset($this->bindingsByTypeName[$typeName][$uuidString]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function hasAnyBinding()
    {
        return count($this->bindings) > 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasBindingsThatMatch(Expression $expr)
    {
        return count($this->filterBindings($this->bindings, $expr)) > 0;
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

        return count($this->filterBindings($this->bindingsByTypeName[$typeName], $expr)) > 0;
    }
}
