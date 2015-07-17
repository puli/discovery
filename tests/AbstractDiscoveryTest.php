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
use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Api\Binding\ResourceBinding;
use Puli\Discovery\Api\ResourceDiscovery;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Discovery\Binding\LazyBinding;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\InMemoryRepository;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractDiscoveryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param Resource[] $resources
     *
     * @return InMemoryRepository
     */
    protected function createRepository(array $resources = array())
    {
        $repo = new InMemoryRepository();

        foreach ($resources as $resource) {
            $repo->add($resource->getPath(), $resource);
        }

        return $repo;
    }

    /**
     * @param ResourceBinding[] $bindings
     *
     * @return ResourceDiscovery
     */
    abstract protected function createDiscovery(ResourceRepository $repo, array $bindings = array(), array $additionalTypes = array());

    public function testFindByType()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $repo = $this->createRepository(array(
            $resource1 = new TestFile('/file1'),
            $resource2 = new TestFile('/file2'),
        ));

        $discovery = $this->createDiscovery($repo, array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding2 = new EagerBinding('/file2', $resource2, $type1),
            $binding3 = new EagerBinding('/file2', $resource2, $type2),
        ));

        $this->assertBindingsEqual(array($binding1, $binding2), $discovery->findByType('type1'));
        $this->assertBindingsEqual(array($binding3), $discovery->findByType('type2'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\NoSuchTypeException
     * @expectedExceptionMessage foo
     */
    public function testFindByTypeFailsIfUnknownType()
    {
        $repo = $this->createRepository();
        $discovery = $this->createDiscovery($repo);

        $discovery->findByType('foo');
    }

    public function testFindByPath()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $repo = $this->createRepository(array(
            $resource1 = new TestFile('/file1'),
            $resource2 = new TestFile('/data/file2'),
            $resource3 = new TestFile('/data/file3'),
        ));

        $coll = new ArrayResourceCollection(array($resource2, $resource3));

        $discovery = $this->createDiscovery($repo, array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding2 = new EagerBinding('/data/file2', $resource2, $type1),
            $binding3 = new EagerBinding('/data/*', $coll, $type2),
        ));

        $this->assertBindingsEqual(array($binding2, $binding3), $discovery->findByPath('/data/file2'));
        $this->assertBindingsEqual(array($binding3), $discovery->findByPath('/data/file2', 'type2'));
    }

    public function testFindByPathForResourceAddedAfterCreation()
    {
        $type = new BindingType('type');

        $repo = $this->createRepository(array(
            new TestFile('/data/file1'),
        ));

        $discovery = $this->createDiscovery($repo, array(
            $binding = new LazyBinding('/data/*', $repo, $type),
        ));

        $repo->add('/data/file2', new TestFile());

        $this->assertBindingsEqual(array($binding), $discovery->findByPath('/data/file1'));
        $this->assertBindingsEqual(array($binding), $discovery->findByPath('/data/file2'));
        $this->assertBindingsEqual(array($binding), $discovery->findByPath('/data/file2', 'type'));
    }

    public function testFindByPathIgnoresUnboundPath()
    {
        $repo = $this->createRepository(array(new TestFile('/file1')));
        $discovery = $this->createDiscovery($repo);

        $this->assertSame(array(), $discovery->findByPath('/file1'));
    }

    public function testFindByPathIgnoresUnboundPathIfTypeIsPassed()
    {
        $type1 = new BindingType('type1');

        $repo = $this->createRepository(array(new TestFile('/file1')));
        $discovery = $this->createDiscovery($repo, array(), array($type1));

        $this->assertSame(array(), $discovery->findByPath('/file1', 'type1'));
    }

    /**
     * @expectedException \Puli\Repository\Api\ResourceNotFoundException
     * @expectedExceptionMessage /foo/bar
     */
    public function testFindByPathFailsIfNonExistingPath()
    {
        $repo = $this->createRepository();
        $discovery = $this->createDiscovery($repo);

        $this->assertSame(array(), $discovery->findByPath('/foo/bar'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\NoSuchTypeException
     * @expectedExceptionMessage foo
     */
    public function testFindByPathFailsIfUnknownType()
    {
        $repo = $this->createRepository(array(new TestFile('/file1')));
        $discovery = $this->createDiscovery($repo);

        $this->assertSame(array(), $discovery->findByPath('/file1', 'foo'));
    }

    public function testGetBindings()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $repo = $this->createRepository(array(
            $resource1 = new TestFile('/file1'),
            $resource2 = new TestFile('/data/file2'),
            $resource3 = new TestFile('/data/file3'),
        ));

        $coll = new ArrayResourceCollection(array($resource2, $resource3));

        $discovery = $this->createDiscovery($repo, array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding2 = new EagerBinding('/data/file2', $resource2, $type1),
            $binding3 = new EagerBinding('/data/*', $coll, $type2),
        ));

        $this->assertBindingsEqual(array($binding1, $binding2, $binding3), $discovery->getBindings());
    }

    public function testGetBindingsForResourceAddedAfterCreation()
    {
        $type = new BindingType('type');

        $repo = $this->createRepository(array(
            new TestFile('/data/file1'),
        ));

        $discovery = $this->createDiscovery($repo, array(
            $binding = new LazyBinding('/data/*', $repo, $type),
        ));

        $repo->add('/data/file2', new TestFile());

        $this->assertBindingsEqual(array($binding), $discovery->getBindings());
    }

    public function testGetNoBindings()
    {
        $repo = $this->createRepository();
        $discovery = $this->createDiscovery($repo);

        $this->assertSame(array(), $discovery->getBindings());
    }

    public function testGetType()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $repo = $this->createRepository(array(
            $resource1 = new TestFile('/file1'),
            $resource2 = new TestFile('/file2'),
        ));

        $discovery = $this->createDiscovery($repo, array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding3 = new EagerBinding('/file2', $resource2, $type2),
        ));

        $this->assertEquals($type1, $discovery->getDefinedType('type1'));
        $this->assertEquals($type2, $discovery->getDefinedType('type2'));
    }

    public function testGetTypes()
    {
        $type1 = new BindingType('type1');
        $type2 = new BindingType('type2');

        $repo = $this->createRepository(array(
            $resource1 = new TestFile('/file1'),
            $resource2 = new TestFile('/file2'),
        ));

        $discovery = $this->createDiscovery($repo, array(
            $binding1 = new EagerBinding('/file1', $resource1, $type1),
            $binding3 = new EagerBinding('/file2', $resource2, $type2),
        ));

        $this->assertEquals(array('type1' => $type1, 'type2' => $type2), $discovery->getDefinedTypes());
    }

    /**
     * @expectedException \Puli\Discovery\Api\NoSuchTypeException
     * @expectedExceptionMessage foobar
     */
    public function testGetTypeFailsIfUnknownType()
    {
        $repo = $this->createRepository();
        $discovery = $this->createDiscovery($repo);

        $discovery->getDefinedType('foobar');
    }

    public function testIsDefined()
    {
        $type = new BindingType('type');

        $repo = $this->createRepository(array(
            $resource = new TestFile('/file'),
        ));

        $discovery = $this->createDiscovery($repo, array(
            $binding1 = new EagerBinding('/file', $resource, $type),
        ));

        $this->assertTrue($discovery->isTypeDefined('type'));
        $this->assertFalse($discovery->isTypeDefined('foo'));
    }

    /**
     * @param ResourceBinding[] $expected
     * @param mixed             $actual
     */
    private function assertBindingsEqual(array $expected, $actual)
    {
        $this->assertInternalType('array', $actual);
        $this->assertCount(count($expected), $actual);

        foreach ($expected as $key => $expectedBinding) {
            $this->assertArrayHasKey($key, $actual);

            $actualBinding = $actual[$key];
            $this->assertSame($expectedBinding->getQuery(), $actualBinding->getQuery());
            $this->assertSame($expectedBinding->getLanguage(), $actualBinding->getLanguage());
            $this->assertEquals($expectedBinding->getType(), $actualBinding->getType());
            $this->assertEquals($expectedBinding->getParameterValues(), $actualBinding->getParameterValues());
            $this->assertEquals($expectedBinding->getResources(), $actualBinding->getResources());
        }
    }
}
