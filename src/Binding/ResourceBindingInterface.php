<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Binding;

use Puli\Repository\Resource\Collection\ResourceCollectionInterface;
use Puli\Repository\Resource\ResourceInterface;

/**
 * Binds one or more resources to a binding type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface ResourceBindingInterface
{
    /**
     * Returns the path of the binding.
     *
     * The path can be used to obtain the bound resources from a resource
     * repository.
     *
     * @return string The binding path.
     */
    public function getPath();

    /**
     * Returns the first bound resource.
     *
     * This method is mainly useful when only one resource is bound.
     *
     * @return ResourceInterface The first bound resource.
     *
     * @see getResources()
     */
    public function getResource();

    /**
     * Returns the bound resources.
     *
     * @return ResourceCollectionInterface The bound resources.
     *
     * @see getResource()
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
}
