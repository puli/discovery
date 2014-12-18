<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests\Storage;

use Puli\Discovery\Storage\PhpDiscoveryStorage;
use Puli\Discovery\Tests\AbstractResourceDiscoveryTest;
use Puli\Discovery\Tests\Binder\InMemoryBinderTest;
use Puli\Repository\InMemoryRepository;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @runTestsInSeparateProcesses
 */
class PhpDiscoveryStorageTest extends AbstractResourceDiscoveryTest
{
    private $tempDir;

    protected function setUp()
    {
        while (false === @mkdir($this->tempDir = sys_get_temp_dir().'/puli-discovery/PhpDiscoveryStorageTest'.rand(10000, 99999), 0777, true)) {}
    }

    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->tempDir);
    }

    /**
     * {@inheritdoc}
     */
    protected function createDiscovery(array $bindings = array())
    {
        $repo = new InMemoryRepository();
        $binder = InMemoryBinderTest::createBinder($repo, $bindings);
        $storage = new PhpDiscoveryStorage();
        $options = array('path' => $this->tempDir.'/discovery.php');

        $storage->storeDiscovery($binder, $options);

        return $storage->loadDiscovery($repo, $options);
    }

    public function testStoreWithCustomClassName()
    {
        $repo = new InMemoryRepository();
        $binder = InMemoryBinderTest::createBinder($repo);
        $storage = new PhpDiscoveryStorage();
        $options = array(
            'path' => $this->tempDir.'/discovery.php',
            'className' => 'MyGeneratedDiscovery',
        );

        $storage->storeDiscovery($binder, $options);

        $discovery = $storage->loadDiscovery($repo, $options);

        $this->assertInstanceOf('\MyGeneratedDiscovery', $discovery);
    }

    public function testStoreWithCustomClassNameAndNamespace()
    {
        $repo = new InMemoryRepository();
        $binder = InMemoryBinderTest::createBinder($repo);
        $storage = new PhpDiscoveryStorage();
        $options = array(
            'path' => $this->tempDir.'/discovery.php',
            'namespace' => 'Puli\Discovery\Tests',
            'className' => 'MyGeneratedDiscovery',
        );

        $storage->storeDiscovery($binder, $options);

        $discovery = $storage->loadDiscovery($repo, $options);

        $this->assertInstanceOf('Puli\Discovery\Tests\MyGeneratedDiscovery', $discovery);
    }

    public function testCreateDirectoriesIfNecessary()
    {
        $repo = new InMemoryRepository();
        $binder = InMemoryBinderTest::createBinder($repo);
        $storage = new PhpDiscoveryStorage();
        $options = array(
            'path' => $this->tempDir.'/some/directory/discovery.php',
        );

        $storage->storeDiscovery($binder, $options);

        $discovery = $storage->loadDiscovery($repo, $options);

        $this->assertInstanceOf('Puli\Discovery\ResourceDiscovery', $discovery);
    }

    /**
     * @expectedException \Puli\Discovery\Storage\LoadingException
     */
    public function testFailIfPathNotFound()
    {
        $repo = new InMemoryRepository();
        $storage = new PhpDiscoveryStorage();

        $storage->loadDiscovery($repo, array('path' => __DIR__.'/foobar'));
    }

    /**
     * @expectedException \Puli\Discovery\Storage\LoadingException
     */
    public function testFailIfPathDoesNotContainDiscovery()
    {
        $repo = new InMemoryRepository();
        $storage = new PhpDiscoveryStorage();

        $storage->loadDiscovery($repo, array('path' => __DIR__.'/Fixtures/not-a-discovery'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfStoringWithoutPath()
    {
        $repo = new InMemoryRepository();
        $binder = InMemoryBinderTest::createBinder($repo);
        $storage = new PhpDiscoveryStorage();

        $storage->storeDiscovery($binder);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfLoadingWithoutPath()
    {
        $repo = new InMemoryRepository();
        $binder = InMemoryBinderTest::createBinder($repo);
        $storage = new PhpDiscoveryStorage();
        $options = array('path' => $this->tempDir.'/discovery.php');

        $storage->storeDiscovery($binder, $options);

        $storage->loadDiscovery($repo);
    }
}
