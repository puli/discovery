<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests;

use Puli\Discovery\KeyValueStoreDiscovery;
use Puli\Repository\Api\ResourceRepository;
use Webmozart\KeyValueStore\ArrayStore;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class KeyValueStoreDiscoveryLoadedTest extends AbstractEditableDiscoveryTest
{
    private $store;

    protected function setUp()
    {
        parent::setUp();

        $this->store = new ArrayStore();
    }

    protected function createEditableDiscovery(ResourceRepository $repo)
    {
        return new KeyValueStoreDiscovery($repo, $this->store);
    }
}
