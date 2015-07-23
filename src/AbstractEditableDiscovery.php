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

use Puli\Discovery\Api\Binding\ResourceBinding;
use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\Api\NoSuchTypeException;
use Puli\Discovery\Binding\LazyBinding;
use Puli\Repository\Api\ResourceNotFoundException;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\Api\UnsupportedLanguageException;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;

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
     * @var ResourceRepository
     */
    protected $repo;

    /**
     * @var array
     */
    protected $queryIndex = array();

    /**
     * @var array
     */
    protected $typeIndex = array();

    /**
     * Creates a new resource discovery.
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
    public function bind($query, $typeName, array $parameterValues = array(), $language = 'glob')
    {
        if ('glob' !== $language) {
            throw UnsupportedLanguageException::forLanguage($language);
        }

        $type = $this->getDefinedType($typeName);

        // Use a lazy binding, because the resources in the repository may change
        $binding = new LazyBinding($query, $this->repo, $type, $parameterValues, $language);

        if ($this->containsBinding($binding)) {
            return;
        }

        $this->insertBinding($binding);
    }

    /**
     * {@inheritdoc}
     */
    public function unbind($query, $typeName = null, array $parameterValues = null, $language = null)
    {
        if (null !== $language && 'glob' !== $language) {
            throw UnsupportedLanguageException::forLanguage($language);
        }

        if (null !== $typeName) {
            $this->removeBindingsByQueryAndType($query, $typeName, $parameterValues);

            return;
        }

        $this->removeBindingsByQuery($query, $parameterValues);
    }

    /**
     * {@inheritdoc}
     */
    public function findByType($typeName)
    {
        if (!isset($this->typeIndex[$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
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
     * {@inheritdoc}
     */
    public function findByPath($resourcePath, $typeName = null)
    {
        if (!$this->repo->contains($resourcePath)) {
            throw ResourceNotFoundException::forPath($resourcePath);
        }

        if (null === $typeName) {
            return $this->findAllForPath($resourcePath);
        }

        return $this->findByPathAndType($resourcePath, $typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->queryIndex = array();
        $this->typeIndex = array();
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

        if (!isset($this->queryIndex[$binding->getQuery()])) {
            return false;
        }

        foreach ($this->queryIndex[$binding->getQuery()] as $id => $true) {
            if ($this->getBinding($id)->equals($binding)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the bindings for a resource path.
     *
     * @param string $resourcePath The resource path.
     *
     * @return ResourceBinding[] The bindings for that resource path.
     */
    protected function findAllForPath($resourcePath)
    {
        $bindings = array();

        foreach ($this->queryIndex as $query => $ids) {
            if (!$this->resourcePathMatchesQuery($resourcePath, $query)) {
                continue;
            }

            foreach ($ids as $id => $true) {
                $bindings[$id] = $this->getBinding($id);
            }
        }

        return array_values($bindings);
    }

    /**
     * Returns the bindings for a resource path and type.
     *
     * @param string $resourcePath The resource path.
     * @param string $typeName     The type name.
     *
     * @return ResourceBinding[] The matching bindings.
     */
    protected function findByPathAndType($resourcePath, $typeName)
    {
        if (!isset($this->typeIndex[$typeName])) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        $bindings = array();

        foreach ($this->queryIndex as $query => $ids) {
            if (!$this->resourcePathMatchesQuery($resourcePath, $query)) {
                continue;
            }

            foreach ($ids as $id => $true) {
                // Prevent duplicate type comparisons
                if (isset($bindings[$id])) {
                    continue;
                }

                $binding = $this->getBinding($id);

                if ($typeName === $binding->getType()->getName()) {
                    $bindings[$id] = $this->getBinding($id);
                }
            }
        }

        return array_values($bindings);
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

        $this->queryIndex[$binding->getQuery()][$id] = true;

        if (!isset($this->typeIndex[$typeName])) {
            $this->typeIndex[$typeName] = array();
        }

        $this->typeIndex[$typeName][$id] = true;
    }

    /**
     * Removes bindings for a query.
     *
     * @param string     $query           The resource query.
     * @param array|null $parameterValues The parameters values to filter by.
     */
    protected function removeBindingsByQuery($query, array $parameterValues = null)
    {
        if (!isset($this->queryIndex[$query])) {
            return;
        }

        foreach ($this->queryIndex[$query] as $id => $true) {
            $binding = $this->getBinding($id);

            if (null !== $parameterValues && $parameterValues !== $binding->getParameterValues()) {
                continue;
            }

            unset($this->queryIndex[$query][$id]);
            unset($this->typeIndex[$binding->getType()->getName()][$id]);

            $this->removeBinding($id);
        }
    }

    /**
     * Removes bindings for a type.
     *
     * @param string     $typeName        The name of the type.
     * @param array|null $parameterValues The parameters values to filter by.
     */
    protected function removeBindingsByType($typeName, array $parameterValues = null)
    {
        if (!isset($this->typeIndex[$typeName])) {
            return;
        }

        foreach ($this->typeIndex[$typeName] as $id => $true) {
            $binding = $this->getBinding($id);

            if (null !== $parameterValues && $parameterValues !== $binding->getParameterValues()) {
                continue;
            }

            unset($this->typeIndex[$typeName][$id]);
            unset($this->queryIndex[$binding->getQuery()][$id]);

            $this->removeBinding($id);
        }
    }

    /**
     * Removes bindings for a binding path and type.
     *
     * @param string     $query           The resource query.
     * @param string     $typeName        The name of the type.
     * @param array|null $parameterValues The parameters values to filter by.
     */
    protected function removeBindingsByQueryAndType($query, $typeName, array $parameterValues = null)
    {
        if (!isset($this->queryIndex[$query])) {
            return;
        }

        if (!isset($this->typeIndex[$typeName])) {
            return;
        }

        foreach ($this->queryIndex[$query] as $id => $true) {
            $binding = $this->getBinding($id);

            if ($typeName !== $binding->getType()->getName()) {
                continue;
            }

            if (null !== $parameterValues && $parameterValues !== $binding->getParameterValues()) {
                continue;
            }

            unset($this->queryIndex[$query][$id]);
            unset($this->typeIndex[$typeName][$id]);

            $this->removeBinding($id);
        }
    }

    /**
     * Returns whether a resource path matches a query.
     *
     * @param string $resourcePath The resource path.
     * @param string $query        The resource query of a binding.
     *
     * @return bool Returns `true` if the resource path matches the query.
     */
    protected function resourcePathMatchesQuery($resourcePath, $query)
    {
        if (false !== strpos($query, '*')) {
            return Glob::match($resourcePath, $query);
        }

        return $query === $resourcePath || Path::isBasePath($query, $resourcePath);
    }
}
