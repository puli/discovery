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

use Puli\Discovery\Binding\EagerBinding;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Repository\Assert\Assertion;
use Puli\Repository\ResourceRepository;

/**
 * Base class for manageable resource discoveries.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractManageableDiscovery implements ManageableDiscovery
{
    /**
     * @var ResourceRepository
     */
    protected $repo;

    /**
     * @var bool[][]
     */
    protected $pathIndex;

    /**
     * @var bool[][]
     */
    protected $typeIndex;

    /**
     * @var bool[][]
     */
    protected $resourcePathIndex;

    /**
     * Creates a new resource discovery.
     *
     * @param ResourceRepository $repo  The repository to fetch resources from.
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
    public function unbind($path, $typeName = null, $parameters = null)
    {
        Assertion::nullOrIsArray($parameters);

        if (null !== $typeName) {
            $this->removeBindingsByPathAndType($path, $typeName, $parameters);

            return;
        }

        $this->removeBindingsByPath($path, $parameters);
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
     * Returns the binding with the given ID.
     *
     * IDs are simple integers that are used to reference bindings in the
     * indices. IDs should be generated in {@link insertBinding()}.
     *
     * The resource with the given ID is guaranteed to have been inserted before
     * this method is called.
     *
     * @param int $id The ID of the binding.
     *
     * @return ResourceBinding The binding with the ID.
     */
    abstract protected function getBinding($id);

    /**
     * Returns all bindings.
     *
     * @return ResourceBinding[] The bindings.
     */
    abstract protected function getAllBindings();

    /**
     * Inserts a binding.
     *
     * An integer ID should be generated for the binding. You must call
     * {@link updateIndicesForId()} with that ID to update the indices.
     *
     * @param ResourceBinding $binding The binding to insert.
     */
    abstract protected function insertBinding(ResourceBinding $binding);

    /**
     * Removes the binding with the given ID.
     *
     * @param int $id The ID of the binding.
     */
    abstract protected function removeBinding($id);

    /**
     * Returns whether the binder contains a binding equal to the given one.
     *
     * The {@link ResourceBinding::equals()} method is used to compare bindings.
     *
     * @param ResourceBinding $binding A binding to search for.
     *
     * @return bool Returns `true` if an equal binding has been defined.
     */
    protected function containsBinding(ResourceBinding $binding)
    {
        if (!isset($this->typeIndex[$binding->getType()->getName()])) {
            return false;
        }

        if (!isset($this->pathIndex[$binding->getPath()])) {
            return false;
        }

        foreach ($this->pathIndex[$binding->getPath()] as $id => $true) {
            if ($this->getBinding($id)->equals($binding)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the bindings for a type.
     *
     * @param string $typeName The type name.
     *
     * @return ResourceBinding[] The bindings for that type.
     */
    protected function getBindingsByType($typeName)
    {
        if (!isset($this->typeIndex[$typeName])) {
            return array();
        }

        $bindings = array();

        if (isset($this->typeIndex[$typeName])) {
            foreach ($this->typeIndex[$typeName] as $id => $true) {
                $bindings[] = $this->getBinding($id);
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
    protected function getBindingsByResourcePath($resourcePath)
    {
        if (!isset($this->resourcePathIndex[$resourcePath])) {
            return array();
        }

        $bindings = array();

        if (isset($this->resourcePathIndex[$resourcePath])) {
            foreach ($this->resourcePathIndex[$resourcePath] as $id => $true) {
                $bindings[] = $this->getBinding($id);
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
    protected function getBindingsByResourcePathAndType($resourcePath, $typeName)
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
                if ($typeName === $this->getBinding($id)->getType()->getName()) {
                    $bindings[] = $this->getBinding($id);
                }
            }
        }

        return $bindings;
    }

    /**
     * Inserts the given binding ID into the index structures.
     *
     * @param int             $id      The binding ID.
     * @param ResourceBinding $binding The associated binding.
     */
    protected function updateIndicesForId($id, ResourceBinding $binding)
    {
        $typeName = $binding->getType()->getName();

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
     * Removes bindings for a binding path.
     *
     * @param string     $path       The binding path.
     * @param null|array $parameters The binding parameters to filter by.
     */
    protected function removeBindingsByPath($path, $parameters = null)
    {
        if (!isset($this->pathIndex[$path])) {
            return;
        }

        foreach ($this->pathIndex[$path] as $id => $true) {
            $binding = $this->getBinding($id);

            if (null !== $parameters && $parameters !== $binding->getParameters()) {
                continue;
            }

            unset($this->typeIndex[$binding->getType()->getName()][$id]);

            foreach ($binding->getResources() as $resource) {
                unset($this->resourcePathIndex[$resource->getPath()][$id]);
            }

            $this->removeBinding($id);
        }

        unset($this->pathIndex[$path]);
    }

    /**
     * Removes bindings for a binding path and type.
     *
     * @param string     $path       The binding path.
     * @param string     $typeName   The name of the type.
     * @param null|array $parameters The binding parameters to filter by.
     */
    protected function removeBindingsByPathAndType($path, $typeName, $parameters = null)
    {
        if (!isset($this->pathIndex[$path])) {
            return;
        }

        if (!isset($this->typeIndex[$typeName])) {
            return;
        }

        foreach ($this->pathIndex[$path] as $id => $true) {
            $binding = $this->getBinding($id);

            if ($typeName !== $binding->getType()->getName()) {
                continue;
            }

            if (null !== $parameters && $parameters !== $binding->getParameters()) {
                continue;
            }

            unset($this->pathIndex[$path][$id]);
            unset($this->typeIndex[$typeName][$id]);

            foreach ($binding->getResources() as $resource) {
                unset($this->resourcePathIndex[$resource->getPath()][$id]);
            }

            $this->removeBinding($id);
        }
    }
}
