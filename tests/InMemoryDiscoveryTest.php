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
use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\ManageableDiscovery;
use Puli\Discovery\Tests\AbstractDiscoveryTest;
use Puli\Repository\InMemoryRepository;
use Puli\Repository\ManageableRepository;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryDiscoveryTest extends AbstractManageableDiscoveryTest
{
    protected function createManageableDiscovery()
    {
        return new InMemoryDiscovery($this->repo);
    }
}
