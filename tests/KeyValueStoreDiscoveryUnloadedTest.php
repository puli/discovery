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

use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\KeyValueStoreDiscovery;
use Puli\Repository\Api\ResourceRepository;
use Webmozart\KeyValueStore\ArrayStore;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class KeyValueStoreDiscoveryUnloadedTest extends AbstractEditableDiscoveryTest
{
    private $store;

    protected function setUp()
    {
        parent::setUp();

        $this->store = new ArrayStore();
    }

    protected function createEditableDiscovery(ResourceRepository $repo)
    {
        $discovery = new KeyValueStoreDiscovery($repo, $this->store);
        $discovery->attachedRepo = $repo;

        return $discovery;
    }

    protected function getDiscoveryUnderTest(EditableDiscovery $discovery)
    {
        // Create a new instance which reads from the store instead of from
        // memory
        return new KeyValueStoreDiscovery($discovery->attachedRepo, $this->store);
    }
}
