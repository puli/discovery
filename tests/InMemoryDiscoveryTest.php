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
use Puli\Discovery\InMemoryDiscovery;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryDiscoveryTest extends AbstractEditableDiscoveryTest
{
    protected function createDiscovery(array $initializers = array())
    {
        return new InMemoryDiscovery();
    }

    protected function loadDiscoveryFromStorage(EditableDiscovery $discovery, array $initializers = array())
    {
        return $discovery;
    }
}
