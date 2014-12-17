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

use Puli\Discovery\Binding\ResourceBindingInterface;

/**
 * Discovers resources for binding types.
 *
 * Use {@link find()} to query all bindings bound to a binding type:
 *
 * ```php
 * $bindings = $binder->find('acme/xliff-messages');
 *
 * foreach ($bindings as $binding) {
 *     foreach ($binding->getResources() as $resource) {
 *         $translator->addXlfCatalog(
 *             $resource->getLocalPath(),
 *             $binding->getParameter('translationDomain')
 *         );
 *     }
 * }
 * ```
 *
 * Use {@link getBindings()} if you want to retrieve all bindings for a
 * specific resource:
 *
 * ```php
 * $bindings = $binder->getBindings('/app/trans/errors.fr.xlf');
 * ```
 *
 * That method optionally lets you filter the bindings for that resource by
 * their binding type:
 *
 * ```php
 * $bindings = $binder->getBindings('/app/trans/errors.fr.xlf', 'acme/xliff-messages');
 * ```
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ResourceDiscoveryInterface
{
    /**
     * Returns all bindings bound to a binding type.
     *
     * @param string $typeName The name of the binding type.
     *
     * @return ResourceBindingInterface[] The matching bindings.
     */
    public function find($typeName);

    /**
     * Returns all bindings.
     *
     * You can filter the returned bindings by resource path and type name.
     * Both arguments are optional.
     *
     * @param string|null $resourcePath The canonical path to a resource.
     * @param string|null $typeName     The name of a binding type.
     *
     * @return ResourceBindingInterface[] The matching bindings.
     */
    public function getBindings($resourcePath = null, $typeName = null);
}
