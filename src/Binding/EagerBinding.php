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
 * Binds resources to a type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EagerBinding extends AbstractBinding
{
    /**
     * @var ResourceCollectionInterface
     */
    private $resources;

    /**
     * Creates a new binding.
     *
     * @param string                                        $path       The path of the binding.
     * @param ResourceInterface|ResourceCollectionInterface $resources  The resources to bind.
     * @param BindingType                                   $type       The type to bind against.
     * @param array                                         $parameters Additional parameters.
     *
     * @throws BindingException If the binding fails.
     * @throws \InvalidArgumentException If the resources are invalid.
     */
    public function __construct($path, $resources, BindingType $type, array $parameters = array())
    {
        if ($resources instanceof ResourceInterface) {
            $resources = new ResourceCollection(array($resources));
        }

        if (!$resources instanceof ResourceCollectionInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Expected resources of type ResourceInterface or '.
                'ResourceCollectionInterface. Got: %s',
                is_object($resources) ? get_class($resources) : gettype($resources)
            ));
        }

        if (0 === count($resources)) {
            throw new BindingException('Did not find any resources to bind.');
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
