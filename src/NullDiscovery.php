<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery;

use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\Api\NoSuchTypeException;

/**
 * A discovery that does nothing.
 *
 * This discovery can be used if you need to inject a discovery instance in
 * some code, but you don't want that discovery to do anything (for example
 * in tests).
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullDiscovery implements EditableDiscovery
{
    /**
     * {@inheritdoc}
     */
    public function bind($query, $typeName, array $parameterValues = array(), $language = 'glob')
    {
    }

    /**
     * {@inheritdoc}
     */
    public function unbind($query, $typeName = null, array $parameterValues = null, $language = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function defineType($type)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function undefineType($typeName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function find($typeName)
    {
        throw NoSuchTypeException::forTypeName($typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings($resourcePath = null, $typeName = null)
    {
        if (null !== $typeName) {
            throw NoSuchTypeException::forTypeName($typeName);
        }

        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function isTypeDefined($typeName)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinedType($typeName)
    {
        throw NoSuchTypeException::forTypeName($typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinedTypes()
    {
        return array();
    }
}
