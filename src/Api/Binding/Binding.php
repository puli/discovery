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

use Puli\Discovery\Api\Binding\Initializer\NotInitializedException;
use Puli\Discovery\Api\Type\BindingNotAcceptedException;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\MissingParameterException;
use Puli\Discovery\Api\Type\NoSuchParameterException;
use Rhumsaa\Uuid\Uuid;
use Serializable;

/**
 * Binds an artifact to a binding type.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Binding extends Serializable
{
    /**
     * Initializes the binding.
     *
     * This method must be called after constructing or unserializing the
     * binding.
     *
     * @param BindingType $type The binding type.
     *
     * @throws NoSuchParameterException    If a parameter is set that does not
     *                                     exist on the loaded type.
     * @throws MissingParameterException   If a required parameter of the loaded
     *                                     type is not set on the binding.
     * @throws BindingNotAcceptedException If the passed type does not accept
     *                                     the binding.
     */
    public function initialize(BindingType $type);

    /**
     * Returns whether the binding is initialized.
     *
     * @return bool Returns `true` if the binding is initialized and `false`
     *              otherwise.
     */
    public function isInitialized();

    /**
     * Returns the UUID of the binding.
     *
     * @return Uuid The binding UUID.
     */
    public function getUuid();

    /**
     * Returns the name of the bound type.
     *
     * @return string The name of the bound type.
     */
    public function getTypeName();

    /**
     * Returns the bound type.
     *
     * @return BindingType The bound type.
     *
     * @throws NotInitializedException If the binding has not yet been
     *                                 initialized.
     */
    public function getType();

    /**
     * Returns the parameters of the binding.
     *
     * @param bool $includeDefault Whether to include the default values set
     *                             in the binding type.
     *
     * @return array The parameter values of the binding.
     */
    public function getParameterValues($includeDefault = true);

    /**
     * Returns whether parameters are set.
     *
     * @param bool $includeDefault Whether to include the default values set
     *                             in the binding type.
     *
     * @return bool Returns whether the binding has any parameter values set.
     */
    public function hasParameterValues($includeDefault = true);

    /**
     * Returns a parameter with a given name.
     *
     * @param string $parameterName  The parameter name.
     * @param bool   $includeDefault Whether to include the default values set
     *                               in the binding type.
     *
     * @return mixed The value of the parameter.
     *
     * @throws NoSuchParameterException If the parameter does not exist.
     */
    public function getParameterValue($parameterName, $includeDefault = true);

    /**
     * Returns whether the parameter with the given name exists.
     *
     * @param string $parameterName  The parameter name.
     * @param bool   $includeDefault Whether to include the default values set
     *                               in the binding type.
     *
     * @return bool Whether that parameter exists.
     */
    public function hasParameterValue($parameterName, $includeDefault = true);
}
