<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Api\Binding;

use Puli\Repository\Api\ResourceCollection;

/**
 * Binds one or more resources to a binding type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ResourceBinding
{
    /**
     * Returns the query for the resources of the binding.
     *
     * @return string The resource query.
     */
    public function getQuery();

    /**
     * Returns the language of the query.
     *
     * @return string The query language.
     */
    public function getLanguage();

    /**
     * Returns the bound resources.
     *
     * @return ResourceCollection The bound resources.
     */
    public function getResources();

    /**
     * Returns the bound type.
     *
     * @return BindingType The bound type.
     */
    public function getType();

    /**
     * Returns the parameters of the binding.
     *
     * @return array The parameter values of the binding.
     */
    public function getParameters();

    /**
     * Returns a parameter with a given name.
     *
     * @param string $parameter The parameter name.
     *
     * @return mixed The value of the parameter.
     *
     * @throws NoSuchParameterException If the parameter does not exist.
     */
    public function getParameter($parameter);

    /**
     * Returns whether the parameter with the given name exists.
     *
     * @param string $parameter The parameter name.
     *
     * @return bool Whether that parameter exists.
     */
    public function hasParameter($parameter);

    /**
     * Returns whether two bindings are equal.
     *
     * @param ResourceBinding $binding A binding to compare.
     *
     * @return bool Returns `true` if the two bindings are equal.
     */
    public function equals(ResourceBinding $binding);
}
