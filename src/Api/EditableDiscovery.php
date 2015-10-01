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
use Puli\Discovery\Api\Type\BindingNotAcceptedException;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\DuplicateTypeException;
use Puli\Discovery\Api\Type\MissingParameterException;
use Puli\Discovery\Api\Type\NoSuchParameterException;
use Puli\Discovery\Api\Type\NoSuchTypeException;
use Rhumsaa\Uuid\Uuid;
use Webmozart\Expression\Expression;

/**
 * A discovery that supports the addition and removal of bindings and types.
 *
 * Binding types have a name and optionally one or more parameters. Binding
 * types can be added with the {@link addBindingType()} method:
 *
 * ```php
 * use Puli\Discovery\Api\Type\BindingParameter;
 * use Puli\Discovery\Api\Type\BindingType;
 *
 * $discovery->addBindingType(new BindingType('acme/message-catalog', array(
 *     new BindingParameter('translationDomain', null, 'messages'),
 * ));
 * ```
 *
 * Bindings can be added for these types with the {@link addBinding()} method:
 *
 * ```php
 * $discovery->addBinding(
 *     new ResourceBinding('/app/trans/errors.*.xlf', 'acme/xliff-messages', array(
 *         'translationDomain' => 'errors',
 *     ),
 * );
 * ```
 *
 * Use {@link findBindings()} to retrieve bindings for a given type:
 *
 * ```php
 * $bindings = $discovery->findBindings('acme/xliff-messages');
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
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface EditableDiscovery extends Discovery
{
    /**
     * Adds a binding to the discovery.
     *
     * The type of the binding must have been added to the discovery.
     *
     * @param Binding $binding The binding to add.
     *
     * @throws NoSuchParameterException    If an invalid parameter was passed.
     * @throws MissingParameterException   If a required parameter was not passed.
     * @throws NoSuchTypeException         If the type of the binding does not
     *                                     exist.
     * @throws BindingNotAcceptedException If the type of the binding does not
     *                                     accept the binding.
     */
    public function addBinding(Binding $binding);

    /**
     * Removes a binding from the discovery.
     *
     * This method does nothing if the given UUID is not found.
     *
     * @param Uuid $uuid The UUID of the binding.
     */
    public function removeBinding(Uuid $uuid);

    /**
     * Removes all bindings from the discovery.
     *
     * You can optionally filter bindings by type and parameter values. If you
     * pass parameter values, you must pass a type as well.
     *
     * If no matching bindings are found or if the type does not exist this
     * method does nothing.
     *
     * @param string|null     $typeName The name of the binding type or `null`
     *                                  to remove all bindings.
     * @param Expression|null $expr     The expression to filter by.
     */
    public function removeBindings($typeName = null, Expression $expr = null);

    /**
     * Adds a binding type to the discovery.
     *
     * @param BindingType $type The type to add.
     *
     * @throws DuplicateTypeException If a binding type with the same name exists.
     */
    public function addBindingType(BindingType $type);

    /**
     * Removes a binding type from the discovery.
     *
     * If the binding type is not found, this method does nothing.
     *
     * All bindings for the type are removed as well.
     *
     * @param string $typeName The name of the binding type.
     */
    public function removeBindingType($typeName);

    /**
     * Removes all binding types and bindings from the discovery.
     */
    public function removeBindingTypes();
}
