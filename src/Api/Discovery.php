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

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\NoSuchBindingException;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\NoSuchTypeException;
use Rhumsaa\Uuid\Uuid;
use Webmozart\Expression\Expression;

/**
 * Discovers artifacts.
 *
 * The discovery allows to bind and retrieve "artifacts" using binding types
 * known to the binder and the retriever. Artifacts can be anything, for
 * example class names or Puli resources.
 *
 * Bindings can be accessed with the {@link findBindings()} method. Here is an
 * example for accessing class bindings bound to a binding type with the name
 * `Example\Engine\Plugin`:
 *
 * ```php
 * use Example\Engine\Plugin;
 *
 * foreach ($discovery->findBindings(Plugin::class) as $binding) {
 *     $className = $binding->getClassName();
 *
 *     // instantiate the plugin
 *     $plugin = new $className();
 *
 *     // ...
 * }
 * ```
 *
 * Use instances of {@link EditableDiscovery} to add bindings and binding types
 * to a discovery.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Discovery
{
    /**
     * Returns all bindings bound to the given binding type.
     *
     * You can optionally pass parameter values to only return bindings with
     * the given parameter values.
     *
     * This method returns an empty array if the given type is not defined.
     *
     * @param string          $typeName The name of the binding type.
     * @param Expression|null $expr     The expression to filter by.
     *
     * @return Binding[] The matching bindings.
     */
    public function findBindings($typeName, Expression $expr = null);

    /**
     * Returns whether the discovery contains bindings.
     *
     * You can optionally pass the name of a binding type and parameter values
     * to only check for bindings with that binding type/parameter values.
     * If you pass parameter values, you must also pass a type name.
     *
     * This method returns `false` if the passed type does not exist.
     *
     * @param string|null     $typeName The name of the binding type.
     * @param Expression|null $expr     The expression to filter by.
     *
     * @return bool Returns whether the discovery contains matching bindings.
     */
    public function hasBindings($typeName = null, Expression $expr = null);

    /**
     * Returns all bindings.
     *
     * @return Binding[] The bindings.
     */
    public function getBindings();

    /**
     * Returns whether the discovery contains a binding.
     *
     * @param Uuid $uuid The UUID of the binding.
     *
     * @return bool Returns `true` if a binding with the given UUID exists and
     *              `false` otherwise.
     */
    public function hasBinding(Uuid $uuid);

    /**
     * Returns the binding with the given UUID.
     *
     * @param Uuid $uuid The UUID of the binding.
     *
     * @return Binding The binding.
     *
     * @throws NoSuchBindingException If the binding does not exist.
     */
    public function getBinding(Uuid $uuid);

    /**
     * Returns whether a binding type exists.
     *
     * @param string $typeName The name of a binding type.
     *
     * @return bool Returns `true` if the binding type exists and `false`
     *              otherwise.
     */
    public function hasBindingType($typeName);

    /**
     * Returns the binding type with the given name.
     *
     * @param string $typeName The name of a binding type.
     *
     * @return BindingType The binding type.
     *
     * @throws NoSuchTypeException If a type with that name does not exist.
     */
    public function getBindingType($typeName);

    /**
     * Returns whether any binding types have been defined.
     *
     * @return bool Returns `true` if the discovery contains binding types and
     *              `false` otherwise.
     */
    public function hasBindingTypes();

    /**
     * Returns all defined binding types.
     *
     * @return BindingType[] The defined binding types.
     */
    public function getBindingTypes();
}
