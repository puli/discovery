<?php

/*
 * This file is part of the webmozart/booking package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Binding;

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Type\MissingParameterException;
use Puli\Discovery\Api\Type\NoSuchParameterException;

/**
 * Binds a class name to a binding type.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ClassBinding extends AbstractBinding
{
    /**
     * @var string
     */
    private $className;

    /**
     * Creates a new class binding.
     *
     * @param string $className       The fully-qualified name of the bound
     *                                class.
     * @param string $typeName        The name of the type to bind against.
     * @param array  $parameterValues The values of the parameters defined
     *                                for the type.
     *
     * @throws NoSuchParameterException  If an invalid parameter was passed.
     * @throws MissingParameterException If a required parameter was not passed.
     */
    public function __construct($className, $typeName, array $parameterValues = array())
    {
        parent::__construct($typeName, $parameterValues);

        $this->className = $className;
    }

    /**
     * Returns the name of the bound class.
     *
     * @return string The fully-qualified class name.
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Binding $other)
    {
        if (!parent::equals($other)) {
            return false;
        }

        /* @var ClassBinding $other */
        return $this->className === $other->className;
    }

    /**
     * {@inheritdoc}
     */
    protected function preSerialize(array &$data)
    {
        parent::preSerialize($data);

        $data[] = $this->className;
    }

    /**
     * {@inheritdoc}
     */
    protected function postUnserialize(array &$data)
    {
        $this->className = array_pop($data);

        parent::postUnserialize($data);
    }
}
