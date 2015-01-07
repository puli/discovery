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
use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Api\Binding\ResourceBinding;
use Puli\Discovery\Api\DuplicateTypeException;
use Puli\Discovery\Api\NoSuchTypeException;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Repository\Api\ResourceRepository;
use RuntimeException;

/**
 * A resource discovery that holds the bindings in memory.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryDiscovery extends AbstractEditableDiscovery
{
    /**
     * @var BindingType[]
     */
    private $types;

    /**
     * @var EagerBinding[]
     */
    private $bindings;

    /**
     * @var int
     */
    private $nextId;

    /**
     * {@inheritdoc}
     */
    public function __construct(ResourceRepository $repo)
    {
        parent::__construct($repo);

        $this->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function defineType($type)
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

        if (isset($this->types[$type->getName()])) {
            throw DuplicateTypeException::forTypeName($type->getName());
        }

        $this->types[$type->getName()] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function undefineType($typeName)
    {
        Assertion::string($typeName);

        $this->removeBindingsByType($typeName);

        unset($this->types[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinedType($typeName)
    {
        if (!isset($this->types[$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        return $this->types[$typeName];
    }

    /**
     * {@inheritdoc}
     */
    public function isTypeDefined($typeName)
    {
        return isset($this->types[$typeName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinedTypes()
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        parent::clear();

        $this->types = array();
        $this->bindings = array();
        $this->nextId = 0;
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
