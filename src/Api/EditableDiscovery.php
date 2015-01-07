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

use InvalidArgumentException;
use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Api\Binding\MissingParameterException;
use Puli\Discovery\Api\Binding\NoSuchParameterException;
use Puli\Repository\Api\UnsupportedLanguageException;

/**
 * A discovery that supports the addition and removal of bindings and types.
 *
 * Binding types have a name and optionally one or more parameters. Binding
 * types can be defined with the {@link define()} method:
 *
 * ```php
 * use Puli\Discovery\Api\Binding\BindingParameter;
 * use Puli\Discovery\Api\Binding\BindingType;
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
interface EditableDiscovery extends ResourceDiscovery
{
    /**
     * Binds resources to a type.
     *
     * The type must have been defined. You can pass values for the parameters
     * defined for the type.
     *
     * @param string $query      A query for resources in the repository.
     * @param string $typeName   The type name to bind to.
     * @param array  $parameters Values for the parameters defined for the type.
     * @param string $language   The language of the resource query.
     *
     * @throws NoSuchParameterException If an invalid parameter was passed.
     * @throws MissingParameterException If a required parameter was not passed.
     * @throws NoQueryMatchesException If the query did not return any results.
     * @throws NoSuchTypeException If the passed type does not exist.
     * @throws UnsupportedLanguageException If the passed language is not supported.
     */
    public function bind($query, $typeName, array $parameters = array(), $language = 'glob');

    /**
     * Unbinds a bound query.
     *
     * You can pass any query that was previously passed to {@link bind()}. If
     * the query was not bound, this method does nothing.
     *
     * Pass the parameter `$typeName` if you want to unbind bindings from a
     * specific binding type. If you don't pass this parameter or if you pass
     * `null`, all bindings for the query will be removed.
     *
     * You can restrict bindings to bindings with specific parameters by passing
     * the parameters in `$parameters`. If you leave this parameter empty, the
     * bindings will be removed regardless of their parameters.
     *
     * @param string      $query      The resource query.
     * @param string|null $typeName   The name of a binding type.
     * @param array|null  $parameters The values of the binding parameters.
     */
    public function unbind($query, $typeName = null, array $parameters = null);

    /**
     * Defines a binding type.
     *
     * The type can be passed as string or as an instance of
     * {@link BindingType}. If you want to define parameters for the type, you
     * need to construct an instance of {@link BindingType} manually:
     *
     * ```php
     * use Puli\Discovery\Api\Binding\BindingParameter;
     * use Puli\Discovery\Api\Binding\BindingType;
     *
     * $binder->define(new BindingType('acme/xliff-message', array(
     *     new BindingParameter('translationDomain', null, 'messages'),
     * ));
     * ```
     *
     * @param string|BindingType $type The type name or instance.
     *
     * @throws DuplicateTypeException If the type is already defined.
     * @throws InvalidArgumentException If the type is invalid.
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
     * Removes all defined types and bindings.
     */
    public function clear();
}
