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
use Puli\Discovery\Api\BindingException;
use Puli\Discovery\Api\BindingType;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Resource\Collection\ResourceCollection;
use Puli\Repository\Resource\Resource;

/**
 * Binds resources to a type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EagerBinding extends AbstractBinding
{
    /**
     * @var ResourceCollection
     */
    private $resources;

    /**
     * Creates a new binding.
     *
     * @param string                      $path       The path of the binding.
     * @param Resource|ResourceCollection $resources  The resources to bind.
     * @param BindingType                 $type       The type to bind against.
     * @param array                       $parameters Additional parameters.
     *
     * @throws BindingException If the binding fails.
     * @throws InvalidArgumentException If the resources are invalid.
     */
    public function __construct($path, $resources, BindingType $type, array $parameters = array())
    {
        if ($resources instanceof Resource) {
            $resources = new ArrayResourceCollection(array($resources));
        }

        if (!$resources instanceof ResourceCollection) {
            throw new InvalidArgumentException(sprintf(
                'Expected resources of type ResourceInterface or '.
                'ResourceCollectionInterface. Got: %s',
                is_object($resources) ? get_class($resources) : gettype($resources)
            ));
        }

        if (0 === count($resources)) {
            throw new BindingException(sprintf(
                'Did not find any resources to bind for path "%s".',
                $path
            ));
        }

        parent::__construct($path, $type, $parameters);

        $this->resources = $resources;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return $this->resources;
    }
}
