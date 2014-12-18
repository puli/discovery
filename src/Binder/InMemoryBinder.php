<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Binder;

use Assert\Assertion;
use InvalidArgumentException;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Discovery\Binding\NoSuchTypeException;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Repository\ResourceRepository;

/**
 * A resource binder that holds the bindings in memory.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryBinder implements ResourceBinder
{
    /**
     * @var ResourceRepository
     */
    private $repo;

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
     * @var bool[][]
     */
    private $pathIndex = array();

    /**
     * @var bool[][]
     */
    private $typeIndex = array();

    /**
     * @var bool[][]
     */
    private $resourcePathIndex = array();

    /**
     * Creates a new resource binder.
     *
     * @param ResourceRepository $repo The repository to fetch resources from.
     */
    public function __construct(ResourceRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function bind($path, $typeName, array $parameters = array())
    {
        $type = $this->getType($typeName);
        $resources = $this->repo->find($path);
        $binding = new EagerBinding($path, $resources, $type, $parameters);

        if ($this->containsBinding($binding)) {
            return;
        }

        $this->insertBinding($binding);
    }

    /**
     * {@inheritdoc}
     */
    public function unbind($path, $typeName = null)
    {
        if (null !== $typeName) {
            $this->removeBindingsByPathAndType($path, $typeName);

            return;
        }

        $this->removeBindingsByPath($path);
    }

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
    public function find($typeName)
    {
        return $this->getBindingsByType($typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings($resourcePath = null, $typeName = null)
    {
        if (null === $resourcePath && null === $typeName) {
            return $this->getAllBindings();
        }

        if (null === $resourcePath) {
            return $this->getBindingsByType($typeName);
        }

        if (null === $typeName) {
            return $this->getBindingsByResourcePath($resourcePath);
        }

        return $this->getBindingsByResourcePathAndType($resourcePath, $typeName);
    }

    /**
     * Returns whether the binder contains a binding equal to the given one.
     *
     * The {@link EagerBinding::equals()} method is used to compare bindings.
     *
     * @param EagerBinding $binding A binding to search for.
     *
     * @return bool Returns `true` if an equal binding has been defined.
     */
    private function containsBinding(EagerBinding $binding)
    {
        if (!isset($this->typeIndex[$binding->getType()->getName()])) {
            return false;
        }

        if (!isset($this->pathIndex[$binding->getPath()])) {
            return false;
        }

        foreach ($this->pathIndex[$binding->getPath()] as $id => $true) {
            if ($this->bindings[$id]->equals($binding)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inserts a binding.
     *
     * @param EagerBinding $binding The binding to insert.
     */
    private function insertBinding(EagerBinding $binding)
    {
        $id = $this->nextId++;
        $typeName = $binding->getType()->getName();

        $this->bindings[$id] = $binding;
        $this->pathIndex[$binding->getPath()][$id] = true;

        if (!isset($this->typeIndex[$typeName])) {
            $this->typeIndex[$typeName] = array();
        }

        $this->typeIndex[$typeName][$id] = true;

        foreach ($binding->getResources() as $resource) {
            $resourcePath = $resource->getPath();

            if (!isset($this->resourcePathIndex[$resourcePath])) {
                $this->resourcePathIndex[$resourcePath] = array();
            }

            $this->resourcePathIndex[$resourcePath][$id] = true;
        }
    }

    /**
     * Returns all bindings.
     *
     * @return ResourceBinding[] The bindings.
     */
    private function getAllBindings()
    {
        return array_values($this->bindings);
    }

    /**
     * Returns the bindings for a type.
     *
     * @param string $typeName The type name.
     *
     * @return ResourceBinding[] The bindings for that type.
     */
    private function getBindingsByType($typeName)
    {
        if (!isset($this->typeIndex[$typeName])) {
            return array();
        }

        $bindings = array();

        if (isset($this->typeIndex[$typeName])) {
            foreach ($this->typeIndex[$typeName] as $id => $true) {
                $bindings[] = $this->bindings[$id];
            }
        }

        return $bindings;
    }

    /**
     * Returns the bindings for a resource path.
     *
     * @param string $resourcePath The resource path.
     *
     * @return ResourceBinding[] The bindings for that resource path.
     */
    private function getBindingsByResourcePath($resourcePath)
    {
        if (!isset($this->resourcePathIndex[$resourcePath])) {
            return array();
        }

        $bindings = array();

        if (isset($this->resourcePathIndex[$resourcePath])) {
            foreach ($this->resourcePathIndex[$resourcePath] as $id => $true) {
                $bindings[] = $this->bindings[$id];
            }
        }

        return $bindings;
    }

    /**
     * Returns the bindings for a resource path and type.
     *
     * @param string $resourcePath The resource path.
     * @param string $typeName     The type name.
     *
     * @return ResourceBinding[] The matching bindings.
     */
    private function getBindingsByResourcePathAndType($resourcePath, $typeName)
    {
        if (!isset($this->typeIndex[$typeName])) {
            return array();
        }

        if (!isset($this->resourcePathIndex[$resourcePath])) {
            return array();
        }

        $bindings = array();

        if (isset($this->resourcePathIndex[$resourcePath])) {
            foreach ($this->resourcePathIndex[$resourcePath] as $id => $true) {
                if ($typeName === $this->bindings[$id]->getType()->getName()) {
                    $bindings[] = $this->bindings[$id];
                }
            }
        }

        return $bindings;
    }

    /**
     * Removes bindings for a binding path.
     *
     * @param string $path The binding path.
     */
    private function removeBindingsByPath($path)
    {
        if (!isset($this->pathIndex[$path])) {
            return;
        }

        foreach ($this->pathIndex[$path] as $id => $true) {
            $binding = $this->bindings[$id];

            unset($this->bindings[$id]);
            unset($this->typeIndex[$binding->getType()->getName()][$id]);

            foreach ($binding->getResources() as $resource) {
                unset($this->resourcePathIndex[$resource->getPath()][$id]);
            }
        }

        unset($this->pathIndex[$path]);
    }

    /**
     * Removes bindings for a binding path and type.
     *
     * @param string $path     The binding path.
     * @param string $typeName The name of the type.
     */
    private function removeBindingsByPathAndType($path, $typeName)
    {
        if (!isset($this->pathIndex[$path])) {
            return;
        }

        if (!isset($this->typeIndex[$typeName])) {
            return;
        }

        foreach ($this->pathIndex[$path] as $id => $true) {
            $binding = $this->bindings[$id];

            if ($typeName !== $binding->getType()->getName()) {
                continue;
            }

            unset($this->bindings[$id]);
            unset($this->pathIndex[$path][$id]);
            unset($this->typeIndex[$typeName][$id]);

            foreach ($binding->getResources() as $resource) {
                unset($this->resourcePathIndex[$resource->getPath()][$id]);
            }
        }
    }
}
