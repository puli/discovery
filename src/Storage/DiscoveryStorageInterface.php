<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Storage;

use Puli\Discovery\ResourceDiscoveryInterface;
use Puli\Repository\ResourceRepository;

/**
 * Stores and loads a discovery in/from a storage.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface DiscoveryStorageInterface
{
    /**
     * Persistently stores a resource discovery.
     *
     * @param ResourceDiscoveryInterface $discovery The discovery to store.
     * @param array                      $options   Implementation specific
     *                                              options controlling how to
     *                                              store the discovery.
     */
    public function storeDiscovery(ResourceDiscoveryInterface $discovery, array $options = array());

    /**
     * Loads a discovery from persistent storage.
     *
     * @param ResourceRepository $repo    The repository that the discovery
     *                                    should read from.
     * @param array              $options Implementation specific options
     *                                    controlling how to load the discovery.
     *
     * @return ResourceDiscoveryInterface The loaded discovery.
     *
     * @throws LoadingException If the discovery could not be loaded.
     */
    public function loadDiscovery(ResourceRepository $repo, array $options = array());
}
