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
use Puli\Discovery\Api\Type\BindingParameter;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\ClassBinding;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\KeyValueStoreDiscovery;
use Puli\Discovery\Tests\Fixtures\Foo;
use Webmozart\KeyValueStore\SerializingArrayStore;

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

        $this->store = new SerializingArrayStore();
    }

    protected function createDiscovery(array $initializers = array())
    {
        return new KeyValueStoreDiscovery($this->store, $initializers);
    }

    protected function loadDiscoveryFromStorage(EditableDiscovery $discovery, array $initializers = array())
    {
        return new KeyValueStoreDiscovery($this->store, $initializers);
    }

    public function testAddBindingKeepsStoredBindings()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding($binding1 = new ResourceBinding('/path1', Foo::clazz));

        $discovery = $this->loadDiscoveryFromStorage($discovery);
        $discovery->addBinding($binding2 = new ClassBinding(__CLASS__, Foo::clazz));

        $this->assertEquals(array($binding1, $binding2), $discovery->getBindings());
    }

    public function testAddBindingInitializesLoadedBindings()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz);
        $binding2 = new ResourceBinding('/path2', Foo::clazz);

        $this->initializer->expects($this->once())
            ->method('acceptsBinding')
            ->willReturn(true);

        $this->initializer->expects($this->exactly(2))
            ->method('initializeBinding')
            ->withConsecutive(
                array($binding1),
                array($binding2)
            );

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding($binding1);

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->addBinding($binding2);
    }

    public function testRemoveBindingKeepsStoredBindings()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding($binding1 = new ResourceBinding('/path1', Foo::clazz));
        $discovery->addBinding($binding2 = new ClassBinding(__CLASS__, Foo::clazz));

        $discovery = $this->loadDiscoveryFromStorage($discovery);
        $discovery->removeBinding($binding1->getUuid());

        $this->assertEquals(array($binding2), $discovery->getBindings());
        $this->assertFalse($discovery->hasBinding($binding1->getUuid()));
        $this->assertTrue($discovery->hasBinding($binding2->getUuid()));
    }

    public function testRemoveBindingInitializesLoadedBindings()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz);
        $binding2 = new ResourceBinding('/path2', Foo::clazz);

        $this->initializer->expects($this->once())
            ->method('acceptsBinding')
            ->willReturn(true);

        $this->initializer->expects($this->exactly(2))
            ->method('initializeBinding')
            ->withConsecutive(
                array($binding1),
                array($binding2)
            );

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->removeBinding($binding1->getUuid());
    }

    public function testRemoveBindingsDoesNotInitializeLoadedBindings()
    {
        $this->initializer->expects($this->never())
            ->method('acceptsBinding');

        $this->initializer->expects($this->never())
            ->method('initializeBinding');

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding(new ResourceBinding('/path1', Foo::clazz));
        $discovery->addBinding(new ClassBinding(__CLASS__, Foo::clazz));

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->removeBindings();
    }

    public function testRemoveBindingsWithTypeDoesNotInitializeLoadedBindings()
    {
        $this->initializer->expects($this->never())
            ->method('acceptsBinding');

        $this->initializer->expects($this->never())
            ->method('initializeBinding');

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding(new ResourceBinding('/path1', Foo::clazz));
        $discovery->addBinding(new ClassBinding(__CLASS__, Foo::clazz));

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->removeBindings(Foo::clazz);
    }

    public function testRemoveBindingsWithTypeAndParameterWorksOnLoadedDiscovery()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz, array('param2' => 'bar'));
        $binding2 = new ResourceBinding('/path2', Foo::clazz);
        $binding3 = new ResourceBinding('/path3', Foo::clazz, array('param1' => 'bar'));

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::OPTIONAL, 'foo'),
            new BindingParameter('param2'),
        )));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);
        $discovery->addBinding($binding3);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        // Bindings need to be initialized for this to work
        $discovery->removeBindings(Foo::clazz, array('param1' => 'foo'));

        $this->assertEquals(array($binding3), $discovery->findBindings(Foo::clazz));
        $this->assertEquals(array($binding3), $discovery->getBindings());
        $this->assertFalse($discovery->hasBinding($binding1->getUuid()));
        $this->assertFalse($discovery->hasBinding($binding2->getUuid()));
        $this->assertTrue($discovery->hasBinding($binding3->getUuid()));
    }

    public function testRemoveBindingTypeDoesNotInitializeLoadedBindings()
    {
        $this->initializer->expects($this->never())
            ->method('acceptsBinding');

        $this->initializer->expects($this->never())
            ->method('initializeBinding');

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding(new ResourceBinding('/path1', Foo::clazz));
        $discovery->addBinding(new ClassBinding(__CLASS__, Foo::clazz));

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->removeBindingType(Foo::clazz);
    }

    public function testGetBindingInitializesLoadedBindings()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz);
        $binding2 = new ResourceBinding('/path2', Foo::clazz);

        $this->initializer->expects($this->once())
            ->method('acceptsBinding')
            ->willReturn(true);

        $this->initializer->expects($this->exactly(2))
            ->method('initializeBinding')
            ->withConsecutive(
                array($binding1),
                array($binding2)
            );

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->getBinding($binding1->getUuid());
    }

    public function testFindBindingsInitializesLoadedBindings()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz);
        $binding2 = new ResourceBinding('/path2', Foo::clazz);

        $this->initializer->expects($this->once())
            ->method('acceptsBinding')
            ->willReturn(true);

        $this->initializer->expects($this->exactly(2))
            ->method('initializeBinding')
            ->withConsecutive(
                array($binding1),
                array($binding2)
            );

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->findBindings(Foo::clazz);
    }

    public function testGetBindingsInitializesLoadedBindings()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz);
        $binding2 = new ResourceBinding('/path2', Foo::clazz);

        $this->initializer->expects($this->once())
            ->method('acceptsBinding')
            ->willReturn(true);

        $this->initializer->expects($this->exactly(2))
            ->method('initializeBinding')
            ->withConsecutive(
                array($binding1),
                array($binding2)
            );

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);

        $discovery = $this->loadDiscoveryFromStorage($discovery, array($this->initializer));
        $discovery->getBindings();
    }
}
