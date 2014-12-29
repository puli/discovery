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

use Puli\Discovery\Api\BindingException;
use Puli\Discovery\Api\BindingType;
use Puli\Repository\Api\ResourceCollection;
use Puli\Repository\Api\ResourceRepository;

/**
 * Binds lazily resources loaded to a type.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LazyBinding extends AbstractBinding
{
    /**
     * @var ResourceCollection
     */
    private $resources;

    /**
     * @var ResourceRepository
     */
    private $repo;

    /**
     * Creates a new binding.
     *
     * @param string             $query      The resource query.
     * @param string             $language   The language of the resource query.
     * @param ResourceRepository $repo       The repository to load the
     *                                       resources from.
     * @param BindingType        $type       The type to bind against.
     * @param array              $parameters Additional parameters.
     *
     * @throws BindingException If the binding fails.
     */
    public function __construct($query, $language, ResourceRepository $repo, BindingType $type, array $parameters = array())
    {
        parent::__construct($query, $language, $type, $parameters);

        $this->repo = $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        if (null === $this->resources) {
            $this->resources = $this->repo->find($this->getQuery());
        }

        return $this->resources;
    }
}
