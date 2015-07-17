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
use Puli\Repository\Api\ResourceRepository;

/**
 * Binds lazily resources loaded to a type.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LazyBinding extends AbstractBinding
{
    /**
     * @var ResourceRepository
     */
    private $repo;

    /**
     * Creates a new binding.
     *
     * @param string             $query      The resource query.
     * @param ResourceRepository $repo       The repository to load the
     *                                       resources from.
     * @param BindingType        $type       The type to bind against.
     * @param array              $parameters Additional parameters.
     * @param string             $language   The language of the resource query.
     *
     * @throws NoSuchParameterException  If an invalid parameter was passed.
     * @throws MissingParameterException If a required parameter was not passed.
     */
    public function __construct($query, ResourceRepository $repo, BindingType $type, array $parameters = array(), $language = 'glob')
    {
        parent::__construct($query, $type, $parameters, $language);

        $this->repo = $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return $this->repo->find($this->getQuery());
    }
}
