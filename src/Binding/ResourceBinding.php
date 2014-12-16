<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Binding;

use Puli\Repository\Resource\Collection\ResourceCollectionInterface;
use Puli\Repository\Resource\ResourceInterface;

/**
 * Bind one or more resources to a binding type.
 *
 * You can optionally pass parameters that apply to the binding. The parameters
 * must have been defined in the bound type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResourceBinding
{
    /**
     * @var ResourceCollectionInterface
     */
    private $resources;

    /**
     * @var BindingType
     */
    private $type;

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * Creates a new binding.
     *
     * You can pass parameters that have been defined for the type. If you pass
     * unknown parameters, or if a required parameter is missing, an exception
     * is thrown.
     *
     * All parameters that you do not set here will receive the default values
     * set for the parameter.
     *
     * @param ResourceCollectionInterface $resources  The resources to bind.
     * @param BindingType                 $type       The type to bind against.
     * @param array                       $parameters Additional parameters.
     *
     * @throws BindingException If the binding fails.
     */
    public function __construct(ResourceCollectionInterface $resources, BindingType $type, array $parameters = array())
    {
        if (0 === count($resources)) {
            throw new BindingException('Did not find any resources to bind.');
        }

        foreach ($parameters as $name => $value) {
            if (!$type->hasParameter($name)) {
                throw new NoSuchParameterException(sprintf(
                    'The parameter "%s" does not exist on type "%s".',
                    $name,
                    $type->getName()
                ));
            }
        }

        foreach ($type->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if (!isset($parameters[$parameterName])) {
                if ($parameter->isRequired()) {
                    throw new MissingParameterException(sprintf(
                        'The required binding parameter "%s" is missing.',
                        $parameterName
                    ));
                }

                $parameters[$parameterName] = $parameter->getDefaultValue();
            }
        }

        $this->resources = $resources;
        $this->type = $type;
        $this->parameters = $parameters;
    }

    /**
     * Returns the first resource of the binding.
     *
     * This method is mainly useful when only one resource is bound.
     *
     * @return ResourceInterface The first bound resource.
     *
     * @see getResources()
     */
    public function getResource()
    {
        $keys = $this->resources->keys();

        return $this->resources->get(reset($keys));
    }

    /**
     * Returns the bound resources.
     *
     * @return ResourceCollectionInterface The bound resources.
     *
     * @see getResource()
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Returns the bound type.
     *
     * @return BindingType The bound type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the parameters of the binding.
     *
     * @return array The parameters of the binding.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($parameter)
    {
        if (!array_key_exists($parameter, $this->parameters)) {
            throw new NoSuchParameterException(sprintf(
                'The parameter "%s" does not exist on type "%s".',
                $parameter,
                $this->type->getName()
            ));
        }

        return $this->parameters[$parameter];
    }

    public function hasParameter($parameter)
    {
        return array_key_exists($parameter, $this->parameters);
    }
}
