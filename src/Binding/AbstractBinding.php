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

use Puli\Repository\Resource\Collection\ResourceCollection;
use Puli\Repository\Resource\Collection\ResourceCollectionInterface;
use Puli\Repository\Resource\ResourceInterface;

/**
 * Base class for resource bindings.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractBinding implements ResourceBindingInterface
{
    /**
     * @var string
     */
    private $path;

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
     * A binding has a path that matches all contained resources.
     *
     * You can pass parameters that have been defined for the type. If you pass
     * unknown parameters, or if a required parameter is missing, an exception
     * is thrown.
     *
     * All parameters that you do not set here will receive the default values
     * set for the parameter.
     *
     * @param string      $path       The path of the binding.
     * @param BindingType $type       The type to bind against.
     * @param array       $parameters Additional parameters.
     *
     * @throws BindingException If the binding fails.
     */
    public function __construct($path, BindingType $type, array $parameters = array())
    {
        $parameters = $this->validateParameters($type, $parameters);

        $this->path = $path;
        $this->type = $type;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        $resources = $this->getResources()->toArray();

        return reset($resources);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function hasParameter($parameter)
    {
        return array_key_exists($parameter, $this->parameters);
    }

    private function validateParameters(BindingType $type, array $parameters)
    {
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

        return $parameters;
    }
}
