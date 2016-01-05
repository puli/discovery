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

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonDiscoveryLoadedTest extends JsonDiscoveryTest
{
    protected function loadDiscoveryFromStorage(EditableDiscovery $discovery, array $initializers = array())
    {
        return $discovery;
    }

    public function testAddBindingInitializesLoadedBindings()
    {
        // Bindings are already in memory
        return;
    }

    public function testRemoveBindingInitializesLoadedBindings()
    {
        // Bindings are already in memory
        return;
    }

    public function testGetBindingInitializesLoadedBindings()
    {
        // Bindings are already in memory
        return;
    }

    public function testFindBindingsInitializesLoadedBindings()
    {
        // Bindings are already in memory
        return;
    }

    public function testGetBindingsInitializesLoadedBindings()
    {
        // Bindings are already in memory
        return;
    }
}
