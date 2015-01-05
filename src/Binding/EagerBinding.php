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
use Puli\Repository\Api\Resource\Resource;
use Puli\Repository\Api\ResourceCollection;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;

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
     * @param string                      $query      The resource query.
     * @param Resource|ResourceCollection $resources  The resources to bind.
     * @param BindingType                 $type       The type to bind against.
     * @param array                       $parameters Additional parameters.
     * @param string                      $language   The language of the resource query.
     *
     * @throws BindingException If the binding fails.
     * @throws InvalidArgumentException If the resources are invalid.
     */
    public function __construct($query, $resources, BindingType $type, array $parameters = array(), $language = 'glob')
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
                'Did not find any resources to bind for query "%s".',
                $query
            ));
        }

        parent::__construct($query, $type, $parameters, $language);

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
