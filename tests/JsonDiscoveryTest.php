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
use Puli\Discovery\JsonDiscovery;
use Puli\Discovery\Test\AbstractPersistentDiscoveryTest;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonDiscoveryTest extends AbstractPersistentDiscoveryTest
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $path;

    protected function setUp()
    {
        $this->tempDir = TestUtil::makeTempDir('puli-discovery', __CLASS__);
        $this->path = $this->tempDir.'/bindings.json';

        parent::setUp();
    }

    protected function createDiscovery(array $initializers = array())
    {
        return new JsonDiscovery($this->path, $initializers);
    }

    protected function loadDiscoveryFromStorage(EditableDiscovery $discovery, array $initializers = array())
    {
        return new JsonDiscovery($this->path, $initializers);
    }
}
