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

use PHPUnit_Framework_TestCase;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\ResourceDiscovery;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractResourceDiscoveryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param ResourceBinding[] $bindings
     *
     * @return ResourceDiscovery
     */
    abstract protected function createDiscovery(array $bindings = array());

    public function testFind()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $resource1 = new TestFile('/file1');
        $resource2 = new TestFile('/file2');

        $discovery = $this->createDiscovery(array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding2 = new EagerBinding('/file2', $resource2, $type1),
            $binding3 = new EagerBinding('/file2', $resource2, $type2),
        ));

        $this->assertBindingsEqual(array($binding1, $binding2), $discovery->find('type1'));
        $this->assertBindingsEqual(array($binding3), $discovery->find('type2'));
    }

    public function testFindIgnoresUnknownType()
    {
        $discovery = $this->createDiscovery();

        $this->assertSame(array(), $discovery->find('foo'));
    }

    public function testGetBindings()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $resource1 = new TestFile('/file1');
        $resource2 = new TestFile('/data/file2');
        $resource3 = new TestFile('/data/file3');

        $coll = new ArrayResourceCollection(array($resource2, $resource3));

        $discovery = $this->createDiscovery(array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding2 = new EagerBinding('/data/file2', $resource2, $type1),
            $binding3 = new EagerBinding('/data/*', $coll, $type2),
        ));

        $this->assertBindingsEqual(array($binding1, $binding2, $binding3), $discovery->getBindings());
        $this->assertBindingsEqual(array($binding2, $binding3), $discovery->getBindings('/data/file2'));
        $this->assertBindingsEqual(array($binding3), $discovery->getBindings('/data/file2', 'type2'));
        $this->assertBindingsEqual(array($binding1, $binding2), $discovery->getBindings(null, 'type1'));
    }

    public function testGetNoBindings()
    {
        $discovery = $this->createDiscovery();

        $this->assertSame(array(), $discovery->getBindings());
    }

    public function testGetNoBindingsIgnoresUnknownPath()
    {
        $discovery = $this->createDiscovery();

        $this->assertSame(array(), $discovery->getBindings('foo'));
    }

    public function testGetNoBindingsIgnoresUnknownType()
    {
        $discovery = $this->createDiscovery();

        $this->assertSame(array(), $discovery->getBindings(null, 'foo'));
    }

    public function testGetType()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $resource1 = new TestFile('/file1');
        $resource2 = new TestFile('/file2');

        $discovery = $this->createDiscovery(array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding3 = new EagerBinding('/file2', $resource2, $type2),
        ));

        $this->assertEquals($type1, $discovery->getType('type1'));
        $this->assertEquals($type2, $discovery->getType('type2'));
    }

    public function testGetTypes()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $resource1 = new TestFile('/file1');
        $resource2 = new TestFile('/file2');

        $discovery = $this->createDiscovery(array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding3 = new EagerBinding('/file2', $resource2, $type2),
        ));

        $this->assertEquals(array('type1' => $type1, 'type2' => $type2), $discovery->getTypes());
    }

    /**
     * @expectedException \Puli\Discovery\Binding\NoSuchTypeException
     * @expectedExceptionMessage foobar
     */
    public function testGetTypeFailsIfUnknownType()
    {
        $discovery = $this->createDiscovery();

        $discovery->getType('foobar');
    }

    public function testIsDefined()
    {
        $type = new BindingType('type');
        $resource = new TestFile('/file');

        $discovery = $this->createDiscovery(array(
            $binding1 = new EagerBinding('/file', $resource, $type),
        ));

        $this->assertTrue($discovery->isDefined('type'));
        $this->assertFalse($discovery->isDefined('foo'));
    }

    /**
     * @param ResourceBinding[] $expected
     * @param mixed                      $actual
     */
    private function assertBindingsEqual(array $expected, $actual)
    {
        $this->assertInternalType('array', $actual);
        $this->assertCount(count($expected), $actual);

        foreach ($expected as $key => $expectedBinding) {
            $this->assertArrayHasKey($key, $actual);

            $actualBinding = $actual[$key];
            $this->assertSame($expectedBinding->getPath(), $actualBinding->getPath());
            $this->assertEquals($expectedBinding->getType(), $actualBinding->getType());
            $this->assertEquals($expectedBinding->getParameters(), $actualBinding->getParameters());
            $this->assertEquals($expectedBinding->getResource(), $actualBinding->getResource());
            $this->assertEquals($expectedBinding->getResources(), $actualBinding->getResources());
        }
    }
}
