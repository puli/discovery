<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Api;

use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Api\Binding\ResourceBinding;
use Puli\Repository\Api\ResourceNotFoundException;

/**
 * Discovers resources for binding types.
 *
 * Use {@link findByType()} to query all bindings bound to a binding type:
 *
 * ```php
 * $bindings = $discovery->findByType('acme/xliff-messages');
 *
 * foreach ($bindings as $binding) {
 *     foreach ($binding->getResources() as $resource) {
 *         $translator->addXlfCatalog(
 *             $resource->getFilesystemPath(),
 *             $binding->getParameter('translationDomain')
 *         );
 *     }
 * }
 * ```
 *
 * Use {@link findByPath()} if you want to retrieve all bindings for a
 * specific resource:
 *
 * ```php
 * $bindings = $discovery->findByPath('/app/trans/errors.fr.xlf');
 * ```
 *
 * That method optionally lets you filter the bindings for that resource by
 * their binding type:
 *
 * ```php
 * $bindings = $discovery->findByPath('/app/trans/errors.fr.xlf', 'acme/xliff-messages');
 * ```
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ResourceDiscovery
{
    /**
     * Returns all bindings bound to a binding type.
     *
     * @param string $typeName The name of the binding type.
     *
     * @return ResourceBinding[] The matching bindings.
     *
     * @throws NoSuchTypeException If the type has not been defined.
     */
    public function findByType($typeName);

    /**
     * Returns all bindings for the given resource path.
     *
     * You can optionally filter the bindings by binding type.
     *
     * @param string      $resourcePath The canonical path to a resource.
     * @param string|null $typeName     The name of a binding type.
     *
     * @return ResourceBinding[] The matching bindings.
     *
     * @throws ResourceNotFoundException If the path does not exist.
     * @throws NoSuchTypeException       If the type has not been defined.
     */
    public function findByPath($resourcePath, $typeName = null);

    /**
     * Returns all bindings.
     *
     * @return ResourceBinding[] The bindings.
     */
    public function getBindings();

    /**
     * Returns whether a binding type has been defined.
     *
     * @param string $typeName The name of a binding type.
     *
     * @return bool Returns `true` if the binding type has been defined.
     */
    public function isTypeDefined($typeName);

    /**
     * Returns the binding type with a given name.
     *
     * @param string $typeName The name of a binding type.
     *
     * @return BindingType The matching binding type.
     *
     * @throws NoSuchTypeException If a type with that name has not been defined.
     */
    public function getDefinedType($typeName);

    /**
     * Returns all defined binding types.
     *
     * @return BindingType[] The defined binding types.
     */
    public function getDefinedTypes();
}
