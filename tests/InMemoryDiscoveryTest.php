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

use Puli\Discovery\InMemoryDiscovery;
use Puli\Repository\Api\ResourceRepository;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryDiscoveryTest extends AbstractEditableDiscoveryTest
{
    protected function createManageableDiscovery(ResourceRepository $repo)
    {
        return new InMemoryDiscovery($repo);
    }
}
