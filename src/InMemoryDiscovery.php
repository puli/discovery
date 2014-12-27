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

use Assert\Assertion;
use InvalidArgumentException;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Discovery\Binding\NoSuchTypeException;
use Puli\Discovery\Binding\ResourceBinding;
use RuntimeException;

/**
 * A resource discovery that holds the bindings in memory.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryDiscovery extends AbstractManageableDiscovery
{
    /**
     * @var BindingType[]
     */
    private $types = array();

    /**
     * @var EagerBinding[]
     */
    private $bindings = array();

    /**
     * @var int
     */
    private $nextId = 0;

    /**
     * {@inheritdoc}
     */
    public function define($type)
    {
        if (is_string($type)) {
            $type = new BindingType($type);
        }

        if (!$type instanceof BindingType) {
            throw new InvalidArgumentException(sprintf(
                'Expected argument of type string or BindingType. Got: %s',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }

        $this->types[$type->getName()] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function undefine($typeName)
    {
        Assertion::string($typeName);

        unset($this->types[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getType($typeName)
    {
        if (!isset($this->types[$typeName])) {
            throw new NoSuchTypeException(sprintf(
                'The binding type "%s" has not been defined.',
                $typeName
            ));
        }

        return $this->types[$typeName];
    }

    /**
     * {@inheritdoc}
     */
    public function isDefined($typeName)
    {
        return isset($this->types[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllBindings()
    {
        return array_values($this->bindings);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBinding($id)
    {
        if (!isset($this->bindings[$id])) {
            throw new RuntimeException(sprintf(
                'Could not find binding with ID %s.',
                $id
            ));
        }

        return $this->bindings[$id];
    }

    /**
     * {@inheritdoc}
     */
    protected function insertBinding(ResourceBinding $binding)
    {
        $id = $this->nextId++;

        $this->bindings[$id] = $binding;

        $this->updateIndicesForId($id, $binding);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeBinding($id)
    {
        unset($this->bindings[$id]);
    }
}
