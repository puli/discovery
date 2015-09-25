<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Api\Binding\Initializer;

use InvalidArgumentException;
use Puli\Discovery\Api\Binding\Binding;

/**
 * Initializes a {@link Binding} class.
 *
 * Binding initializers can be used to inject dependencies into newly
 * constructed or unserialized {@link Binding} instances.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface BindingInitializer
{
    /**
     * Returns whether the initializer accepts binding of the given class.
     *
     * @param string $className The name of the binding class.
     *
     * @return bool Returns `true` if bindings of that class can be initialized
     *              and `false` otherwise.
     */
    public function acceptsBinding($className);

    /**
     * Initializes a binding.
     *
     * @param Binding $binding The binding to initialize.
     *
     * @throws InvalidArgumentException If the passed binding is not supported.
     *                                  Use {@link acceptsBinding()} to check
     *                                  whether a binding is supported before
     *                                  calling this method.
     */
    public function initializeBinding(Binding $binding);
}
