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
use Webmozart\KeyValueStore\SerializingArrayStore;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class KeyValueStoreDiscoveryLoadedTest extends AbstractEditableDiscoveryTest
{
    private $store;

    protected function setUp()
    {
        parent::setUp();

        $this->store = new SerializingArrayStore();
    }

    protected function createDiscovery(array $initializers = array())
    {
        return new KeyValueStoreDiscovery($this->store, $initializers);
    }

    protected function loadDiscoveryFromStorage(EditableDiscovery $discovery, array $initializers = array())
    {
        return $discovery;
    }
}
