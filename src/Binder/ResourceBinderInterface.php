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

use Puli\Discovery\Binding\BindingException;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\NoSuchTypeException;
use Puli\Discovery\ResourceDiscoveryInterface;

/**
 * Binds resources to binding types.
 *
 * Binding types have a name and optionally one or more parameters. Binding
 * types can be defined with the {@link define()} method:
 *
 * ```php
 * use Puli\Discovery\Binding\BindingParameter;
 * use Puli\Discovery\Binding\BindingType;
 *
 * $binder->define(new BindingType('acme/xliff-messages', array(
 *     new BindingParameter('translationDomain', null, 'messages'),
 * ));
 * ```
 *
 * Resources can be bound to these types with the {@link bind()} method:
 *
 * ```php
 * $binder->bind('/app/trans/errors.*.xlf', 'acme/xliff-messages', array(
 *     'translationDomain' => 'errors',
 * ));
 * ```
 *
 * Use {@link find()} of {@link ResourceDiscoveryInterface} to retrieve
 * bindings for a given type:
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
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ResourceBinderInterface extends ResourceDiscoveryInterface
{
    /**
     * Binds resources to a type.
     *
     * The type must have been defined. You can pass values for the parameters
     * defined for the type.
     *
     * @param string $path       A resource path or a glob pattern. Must start
     *                           with "/". "." and ".." segments in the path are
     *                           supported.
     * @param string $typeName   The type name to bind to.
     * @param array  $parameters Values for the parameters defined for the type.
     *
     * @throws BindingException If the path could not be bound.
     */
    public function bind($path, $typeName, array $parameters = array());

    /**
     * Unbinds a bound path.
     *
     * You can pass any binding path that was previously passed to
     * {@link bind()}. If the path was not bound, this method does nothing.
     *
     * Pass the parameter `$typeName` if you want to unbind bindings from a
     * specific binding type. If you don't pass this parameter or if you pass
     * `null`, the bindings will be unbound from all types.
     *
     * If you want to unbind a specific resource, you need to query the bindings
     * matching the resource path first:
     *
     * ```php
     * $bindings = $binder->getBindings('/path/to/resource');
     *
     * foreach ($bindings as $binding) {
     *     $binder->unbind($binding->getPath(), $binding->getType()->getName());
     * }
     * ```
     *
     * Caution: This will remove the bindings for other resources matched by
     * the same binding path as well.
     *
     * @param string      $path     The binding path.
     * @param string|null $typeName The name of a binding type.
     */
    public function unbind($path, $typeName = null);

    /**
     * Defines a binding type.
     *
     * The type can be passed as string or as an instance of
     * {@link BindingType}. If you want to define parameters for the type, you
     * need to construct an instance of {@link BindingType} manually:
     *
     * ```php
     * use Puli\Discovery\Binding\BindingParameter;
     * use Puli\Discovery\Binding\BindingType;
     *
     * $binder->define(new BindingType('acme/xliff-message', array(
     *     new BindingParameter('translationDomain', null, 'messages'),
     * ));
     * ```
     *
     * @param string|BindingType $type The type name or instance.
     */
    public function define($type);

    /**
     * Undefines a binding type.
     *
     * If the type has not been defined, this method does nothing.
     *
     * @param string $typeName The name of a binding type.
     */
    public function undefine($typeName);

    /**
     * Returns whether a binding type has been defined.
     *
     * @param string $typeName The name of a binding type.
     *
     * @return bool Returns `true` if the binding type has been defined.
     */
    public function isDefined($typeName);

    /**
     * Returns the binding type with a given name.
     *
     * @param string $typeName The name of a binding type.
     *
     * @return BindingType The matching binding type.
     *
     * @throws NoSuchTypeException If a type with that name has not been defined.
     */
    public function getType($typeName);

    /**
     * Returns all defined binding types.
     *
     * @return BindingType[] The defined binding types.
     */
    public function getTypes();
}
