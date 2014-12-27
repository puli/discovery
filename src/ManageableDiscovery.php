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

use Puli\Discovery\Binding\BindingException;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\ResourceDiscovery;

/**
 * A discovery that supports the addition and removal of bindings and types.
 *
 * Binding types have a name and optionally one or more parameters. Binding
 * types can be defined with the {@link define()} method:
 *
 * ```php
 * use Puli\Discovery\Binding\BindingParameter;
 * use Puli\Discovery\Binding\BindingType;
 *
 * $discovery->define(new BindingType('acme/xliff-messages', array(
 *     new BindingParameter('translationDomain', null, 'messages'),
 * ));
 * ```
 *
 * Resources can be bound to these types with the {@link bind()} method:
 *
 * ```php
 * $discovery->bind('/app/trans/errors.*.xlf', 'acme/xliff-messages', array(
 *     'translationDomain' => 'errors',
 * ));
 * ```
 *
 * Use {@link find()} to retrieve bindings for a given type:
 *
 * ```php
 * $bindings = $discovery->find('acme/xliff-messages');
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
interface ManageableDiscovery extends ResourceDiscovery
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
     * `null`, all bindings for the binding path will be removed.
     *
     * You can restrict bindings to bindings with specific parameters by passing
     * the parameters in `$parameters`. If you leave this parameter empty, the
     * bindings will be removed regardless of their parameters.
     *
     * @param string      $path       The binding path.
     * @param string|null $typeName   The name of a binding type.
     * @param array|null  $parameters The values of the binding parameters.
     */
    public function unbind($path, $typeName = null, $parameters = null);

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
}
