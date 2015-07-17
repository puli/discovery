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

use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Api\Binding\MissingParameterException;
use Puli\Discovery\Api\Binding\NoSuchParameterException;
use Puli\Discovery\Api\Binding\ResourceBinding;
use Puli\Discovery\Api\Validation\ConstraintViolation;
use Puli\Discovery\Validation\SimpleParameterValidator;

/**
 * Base class for resource bindings.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractBinding implements ResourceBinding
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $language;

    /**
     * @var BindingType
     */
    private $type;

    /**
     * @var array
     */
    private $parameterValues = array();

    /**
     * Creates a new binding.
     *
     * A binding has a query that is used to retrieve the resources matched
     * by the binding.
     *
     * You can pass parameters that have been defined for the type. If you pass
     * unknown parameters, or if a required parameter is missing, an exception
     * is thrown.
     *
     * All parameters that you do not set here will receive the default values
     * set for the parameter.
     *
     * @param string      $query           The resource query.
     * @param BindingType $type            The type to bind against.
     * @param array       $parameterValues The values of the parameters defined
     *                                     for the type.
     * @param string      $language        The language of the resource query.
     *
     * @throws NoSuchParameterException  If an invalid parameter was passed.
     * @throws MissingParameterException If a required parameter was not passed.
     */
    public function __construct($query, BindingType $type, array $parameterValues = array(), $language = 'glob')
    {
        $this->assertParametersValid($parameterValues, $type);

        $parameterValues = array_replace($type->getParameterValues(), $parameterValues);

        ksort($parameterValues);

        $this->query = $query;
        $this->language = $language;
        $this->type = $type;
        $this->parameterValues = $parameterValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->language;
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
    public function getParameterValues()
    {
        return $this->parameterValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterValue($parameterName)
    {
        if (!array_key_exists($parameterName, $this->parameterValues)) {
            throw NoSuchParameterException::forParameterName($parameterName, $this->type->getName());
        }

        return $this->parameterValues[$parameterName];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterValue($parameterName)
    {
        return array_key_exists($parameterName, $this->parameterValues);
    }

    /**
     * {@inheritdoc}
     */
    public function equals(ResourceBinding $other)
    {
        if (get_class($other) !== get_class($this)) {
            return false;
        }

        if ($this->query !== $other->getQuery()) {
            return false;
        }

        if ($this->type !== $other->getType()) {
            return false;
        }

        if ($this->language !== $other->getLanguage()) {
            return false;
        }

        // The local parameters are sorted by key. Sort before comparing to
        // prevent false negatives.
        $otherParameterValues = $other->getParameterValues();
        ksort($otherParameterValues);

        if ($this->parameterValues !== $otherParameterValues) {
            return false;
        }

        return true;
    }

    private function assertParametersValid(array $parameterValues, BindingType $type)
    {
        $validator = new SimpleParameterValidator();
        $violations = $validator->validate($parameterValues, $type);

        foreach ($violations as $violation) {
            switch ($violation->getCode()) {
                case ConstraintViolation::NO_SUCH_PARAMETER:
                    throw NoSuchParameterException::forParameterName($violation->getParameterName(), $violation->getTypeName());
                case ConstraintViolation::MISSING_PARAMETER:
                    throw MissingParameterException::forParameterName($violation->getParameterName(), $violation->getTypeName());
            }
        }
    }
}
