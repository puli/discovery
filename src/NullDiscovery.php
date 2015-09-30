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

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\NoSuchBindingException;
use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Api\Type\NoSuchTypeException;
use Rhumsaa\Uuid\Uuid;
use Webmozart\Expression\Expression;

/**
 * A discovery that does nothing.
 *
 * This discovery can be used if you need to inject a discovery instance in
 * some code, but you don't want that discovery to do anything (for example
 * in tests).
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullDiscovery implements EditableDiscovery
{
    /**
     * {@inheritdoc}
     */
    public function findBindings($typeName, Expression $expr = null)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindings($typeName = null, Expression $expr = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasBinding(Uuid $uuid)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBinding(Uuid $uuid)
    {
        throw NoSuchBindingException::forUuid($uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingType($typeName)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingType($typeName)
    {
        throw NoSuchTypeException::forTypeName($typeName);
    }

    /**
     * {@inheritdoc}
     */
    public function hasBindingTypes()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingTypes()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function addBinding(Binding $binding)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function removeBinding(Uuid $uuid)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindings($typeName = null, Expression $expr = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addBindingType(BindingType $type)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingType($typeName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function removeBindingTypes()
    {
    }
}
