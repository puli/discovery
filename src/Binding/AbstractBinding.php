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

use InvalidArgumentException;
use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\Initializer\NotInitializedException;
use Puli\Discovery\Api\Type\BindingNotAcceptedException;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\MissingParameterException;
use Puli\Discovery\Api\Type\NoSuchParameterException;
use Rhumsaa\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Base class for bindings.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractBinding implements Binding
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var BindingType|null
     */
    private $type;

    /**
     * @var array
     */
    private $userParameterValues = array();

    /**
     * @var array
     */
    private $parameterValues = array();

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
     * @param string    $typeName        The name of the type to bind against.
     * @param array     $parameterValues The values of the parameters defined
     *                                   for the type.
     * @param Uuid|null $uuid            The UUID of the binding. A new one is
     *                                   generated if none is passed.
     *
     * @throws NoSuchParameterException  If an invalid parameter was passed.
     * @throws MissingParameterException If a required parameter was not passed.
     */
    public function __construct($typeName, array $parameterValues = array(), Uuid $uuid = null)
    {
        Assert::stringNotEmpty($typeName, 'The type name must be a non-empty string. Got: %s');

        ksort($parameterValues);

        $this->typeName = $typeName;
        $this->userParameterValues = $parameterValues;
        $this->parameterValues = $parameterValues;
        $this->uuid = $uuid ?: Uuid::uuid4();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(BindingType $type)
    {
        if ($this->typeName !== $type->getName()) {
            throw new InvalidArgumentException(sprintf(
                'The passed type "%s" does not match the configured type "%s".',
                $type->getName(),
                $this->typeName
            ));
        }

        if (!$type->acceptsBinding(get_class($this))) {
            throw BindingNotAcceptedException::forBindingClass($type->getName(), get_class($this));
        }

        // Merge default parameter values of the type
        $this->assertParameterValuesValid($this->userParameterValues, $type);

        $this->type = $type;
        $this->parameterValues = array_replace($type->getParameterValues(), $this->userParameterValues);

        ksort($this->parameterValues);
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialized()
    {
        return null !== $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        if (null === $this->type) {
            throw new NotInitializedException('The binding must be initialized before accessing the type.');
        }

        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterValues($includeDefault = true)
    {
        if ($includeDefault) {
            return $this->parameterValues;
        }

        return $this->userParameterValues;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterValues($includeDefault = true)
    {
        if ($includeDefault) {
            return count($this->parameterValues) > 0;
        }

        return count($this->userParameterValues) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterValue($parameterName, $includeDefault = true)
    {
        $parameterValues = $includeDefault ? $this->parameterValues : $this->userParameterValues;

        if (!array_key_exists($parameterName, $parameterValues)) {
            throw NoSuchParameterException::forParameterName($parameterName, $this->typeName);
        }

        return $parameterValues[$parameterName];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterValue($parameterName, $includeDefault = true)
    {
        if ($includeDefault) {
            return array_key_exists($parameterName, $this->parameterValues);
        }

        return array_key_exists($parameterName, $this->userParameterValues);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $data = array();

        $this->preSerialize($data);

        return serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->postUnserialize($data);
    }

    protected function preSerialize(array &$data)
    {
        $data[] = $this->typeName;
        $data[] = $this->userParameterValues;
        $data[] = $this->uuid->toString();
    }

    protected function postUnserialize(array &$data)
    {
        $this->uuid = Uuid::fromString(array_pop($data));
        $this->userParameterValues = array_pop($data);
        $this->parameterValues = $this->userParameterValues;
        $this->typeName = array_pop($data);
    }

    private function assertParameterValuesValid(array $parameterValues, BindingType $type)
    {
        foreach ($parameterValues as $name => $value) {
            if (!$type->hasParameter($name)) {
                throw NoSuchParameterException::forParameterName($name, $type->getName());
            }
        }

        foreach ($type->getParameters() as $parameter) {
            if (!isset($parameterValues[$parameter->getName()])) {
                if ($parameter->isRequired()) {
                    throw MissingParameterException::forParameterName($parameter->getName(), $type->getName());
                }
            }
        }
    }
}
