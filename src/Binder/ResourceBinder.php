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

use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Discovery\Binding\NoSuchTypeException;
use Puli\Repository\ResourceRepositoryInterface;

/**
 * A resource binder based on a Puli repository.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResourceBinder implements ResourceBinderInterface
{
    /**
     * @var ResourceRepositoryInterface
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
    private $nextKey = 0;

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
     * @param ResourceRepositoryInterface $repo The repository to fetch
     *                                          resources from.
     */
    public function __construct(ResourceRepositoryInterface $repo)
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
            throw new \InvalidArgumentException(sprintf(
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
        if (!is_string($typeName)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected argument of type string. Got: %s',
                is_object($typeName) ? get_class($typeName) : gettype($typeName)
            ));
        }

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
        if (!isset($this->typeIndex[$typeName])) {
            return array();
        }

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

        foreach ($this->pathIndex[$binding->getPath()] as $key => $true) {
            if ($this->bindings[$key]->equals($binding)) {
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
        $key = $this->nextKey++;
        $typeName = $binding->getType()->getName();

        $this->bindings[$key] = $binding;
        $this->pathIndex[$binding->getPath()][$key] = true;

        if (!isset($this->typeIndex[$typeName])) {
            $this->typeIndex[$typeName] = array();
        }

        $this->typeIndex[$typeName][$key] = true;

        foreach ($binding->getResources() as $resource) {
            $resourcePath = $resource->getPath();

            if (!isset($this->resourcePathIndex[$resourcePath])) {
                $this->resourcePathIndex[$resourcePath] = array();
            }

            $this->resourcePathIndex[$resourcePath][$key] = true;
        }
    }

    /**
     * Returns all bindings.
     *
     * @return EagerBinding[] The bindings.
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
     * @return EagerBinding[] The bindings for that type.
     */
    private function getBindingsByType($typeName)
    {
        $bindings = array();

        if (isset($this->typeIndex[$typeName])) {
            foreach ($this->typeIndex[$typeName] as $key => $true) {
                $bindings[] = $this->bindings[$key];
            }
        }

        return $bindings;
    }

    /**
     * Returns the bindings for a resource path.
     *
     * @param string $resourcePath The resource path.
     *
     * @return EagerBinding[] The bindings for that resource path.
     */
    private function getBindingsByResourcePath($resourcePath)
    {
        $bindings = array();

        if (isset($this->resourcePathIndex[$resourcePath])) {
            foreach ($this->resourcePathIndex[$resourcePath] as $key => $true) {
                $bindings[] = $this->bindings[$key];
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
     * @return EagerBinding[] The matching bindings.
     */
    private function getBindingsByResourcePathAndType($resourcePath, $typeName)
    {
        $bindings = array();

        if (isset($this->resourcePathIndex[$resourcePath])) {
            foreach ($this->resourcePathIndex[$resourcePath] as $key => $true) {
                if ($typeName === $this->bindings[$key]->getType()->getName()) {
                    $bindings[] = $this->bindings[$key];
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

        foreach ($this->pathIndex[$path] as $key => $true) {
            $binding = $this->bindings[$key];

            unset($this->bindings[$key]);
            unset($this->typeIndex[$binding->getType()->getName()][$key]);

            foreach ($binding->getResources() as $resource) {
                unset($this->resourcePathIndex[$resource->getPath()][$key]);
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

        foreach ($this->pathIndex[$path] as $key => $true) {
            $binding = $this->bindings[$key];

            if ($typeName !== $binding->getType()->getName()) {
                continue;
            }

            unset($this->bindings[$key]);
            unset($this->pathIndex[$path][$key]);
            unset($this->typeIndex[$typeName][$key]);

            foreach ($binding->getResources() as $resource) {
                unset($this->resourcePathIndex[$resource->getPath()][$key]);
            }
        }
    }
}
