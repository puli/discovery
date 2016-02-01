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

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\Initializer\BindingInitializer;
use Puli\Discovery\Api\Discovery;
use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\Api\Type\BindingParameter;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\Tests\Fixtures\Bar;
use Puli\Discovery\Tests\Fixtures\Foo;
use stdClass;
use Webmozart\Expression\Expr;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractEditableDiscoveryTest extends AbstractDiscoveryTest
{
    /**
     * Creates a discovery that can be written in the test.
     *
     * @param BindingInitializer[] $initializers
     *
     * @return EditableDiscovery
     */
    abstract protected function createDiscovery(array $initializers = array());

    /**
     * Creates a discovery that can be read in the test.
     *
     * This method is needed to test whether the discovery actually synchronized
     * all in-memory changes to the backing data store:
     *
     *  * If the method returns the passed $discovery, the in-memory data
     *    structures are tested.
     *  * If the method returns a new discovery with the same backing data store,
     *    that data store is tested.
     *
     * @param EditableDiscovery    $discovery
     * @param BindingInitializer[] $initializers
     *
     * @return EditableDiscovery
     */
    abstract protected function loadDiscoveryFromStorage(EditableDiscovery $discovery, array $initializers = array());

    /**
     * @param BindingType[]        $types
     * @param Binding[]            $bindings
     * @param BindingInitializer[] $initializers
     *
     * @return Discovery
     */
    protected function createLoadedDiscovery(array $types = array(), array $bindings = array(), array $initializers = array())
    {
        $discovery = $this->createDiscovery($initializers);

        foreach ($types as $type) {
            $discovery->addBindingType($type);
        }

        foreach ($bindings as $binding) {
            $discovery->addBinding($binding);
        }

        return $this->loadDiscoveryFromStorage($discovery);
    }

    public function testAddBinding()
    {
        $binding = new ResourceBinding('/path', Foo::clazz);

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBinding($binding);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(1, $discovery->findBindings(Foo::clazz));
        $this->assertCount(1, $discovery->getBindings());
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchTypeException
     * @expectedExceptionMessage Foo
     */
    public function testAddBindingFailsIfTypeNotFound()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBinding(new ResourceBinding('/path', Foo::clazz));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\BindingNotAcceptedException
     * @expectedExceptionMessage Foo
     */
    public function testAddBindingFailsIfTypeDoesNotAcceptBinding()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::CLASS_BINDING));
        $discovery->addBinding(new ResourceBinding('/path', Foo::clazz));
    }

    public function testAddBindingIgnoresDuplicates()
    {
        $binding = new ResourceBinding('/path', Foo::clazz);

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBinding($binding);
        $discovery->addBinding($binding);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(1, $discovery->findBindings(Foo::clazz));
        $this->assertCount(1, $discovery->getBindings());
    }

    public function testRemoveBindings()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz);
        $binding2 = new ResourceBinding('/path2', Foo::clazz);

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);
        $discovery->removeBindings();

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(0, $discovery->findBindings(Foo::clazz));
        $this->assertCount(0, $discovery->getBindings());
    }

    public function testRemoveBindingsDoesNothingIfNoneFound()
    {
        $discovery = $this->createDiscovery();
        $discovery->removeBindings();

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(0, $discovery->getBindings());
    }

    public function testRemoveBindingsWithType()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz);
        $binding2 = new ResourceBinding('/path2', Foo::clazz);
        $binding3 = new ResourceBinding('/path3', Bar::clazz);

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBindingType(new BindingType(Bar::clazz, self::RESOURCE_BINDING));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);
        $discovery->addBinding($binding3);
        $discovery->removeBindings(Foo::clazz);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertEquals(array(), $discovery->findBindings(Foo::clazz));
        $this->assertEquals(array($binding3), $discovery->findBindings(Bar::clazz));
        $this->assertEquals(array($binding3), $discovery->getBindings());
    }

    public function testRemoveBindingsWithTypeDoesNothingIfNoneFound()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->removeBindings(Foo::clazz);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(0, $discovery->getBindings());
    }

    public function testRemoveBindingsWithTypeDoesNothingIfTypeNotFound()
    {
        $discovery = $this->createDiscovery();
        $discovery->removeBindings(Foo::clazz);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(0, $discovery->getBindings());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testRemoveBindingsWithTypeFailsIfInvalidType()
    {
        $discovery = $this->createDiscovery();
        $discovery->removeBindings(new stdClass());
    }

    public function testRemoveBindingsWithExpression()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz, array('param1' => 'foo', 'param2' => 'bar'));
        $binding2 = new ResourceBinding('/path2', Foo::clazz, array('param1' => 'foo'));
        $binding3 = new ResourceBinding('/path3', Foo::clazz, array('param1' => 'bar'));

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING, array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        )));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);
        $discovery->addBinding($binding3);
        $discovery->removeBindings(null, Expr::method('getParameterValue', 'param1', Expr::same('foo')));

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertEquals(array($binding3), $discovery->findBindings(Foo::clazz));
        $this->assertEquals(array($binding3), $discovery->getBindings());
    }

    public function testRemoveBindingsWithTypeAndExpression()
    {
        $binding1 = new ResourceBinding('/path1', Foo::clazz, array('param1' => 'foo', 'param2' => 'bar'));
        $binding2 = new ResourceBinding('/path2', Foo::clazz, array('param1' => 'foo'));
        $binding3 = new ResourceBinding('/path3', Foo::clazz, array('param1' => 'bar'));

        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING, array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        )));
        $discovery->addBinding($binding1);
        $discovery->addBinding($binding2);
        $discovery->addBinding($binding3);
        $discovery->removeBindings(Foo::clazz, Expr::method('getParameterValue', 'param1', Expr::same('foo')));

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertEquals(array($binding3), $discovery->findBindings(Foo::clazz));
        $this->assertEquals(array($binding3), $discovery->getBindings());
    }

    public function testRemoveBindingsWithTypeAndParametersDoesNothingIfNoneFound()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->removeBindings(Foo::clazz, Expr::method('getParameterValue', 'param1', Expr::same('foo')));

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(0, $discovery->getBindings());
    }

    public function testRemoveBindingsWithTypeAndParametersDoesNothingIfTypeNotFound()
    {
        $discovery = $this->createDiscovery();
        $discovery->removeBindings(Foo::clazz, Expr::method('getParameterValue', 'param1', Expr::same('foo')));

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(0, $discovery->getBindings());
    }

    public function testAddBindingType()
    {
        $type = new BindingType(Foo::clazz, self::RESOURCE_BINDING);

        $discovery = $this->createDiscovery();
        $discovery->addBindingType($type);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertEquals($type, $discovery->getBindingType(Foo::clazz));
    }

    public function testAddBindingTypeAfterReadingStorage()
    {
        $type1 = new BindingType(Foo::clazz, self::RESOURCE_BINDING);
        $type2 = new BindingType(Bar::clazz, self::RESOURCE_BINDING);

        $discovery = $this->createDiscovery();
        $discovery->addBindingType($type1);

        // Make sure that the previous call to addBindingType() stored all
        // necessary information in order to add further types (e.g. nextId)
        $discovery = $this->loadDiscoveryFromStorage($discovery);
        $discovery->addBindingType($type2);

        $this->assertEquals($type1, $discovery->getBindingType(Foo::clazz));
        $this->assertEquals($type2, $discovery->getBindingType(Bar::clazz));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\DuplicateTypeException
     * @expectedExceptionMessage Foo
     */
    public function testAddBindingTypeFailsIfAlreadyDefined()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
    }

    public function testRemoveBindingType()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType($type1 = new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBindingType(new BindingType(Bar::clazz, self::RESOURCE_BINDING));
        $discovery->removeBindingType(Bar::clazz);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertEquals(array($type1), $discovery->getBindingTypes());
        $this->assertTrue($discovery->hasBindingType(Foo::clazz));
        $this->assertFalse($discovery->hasBindingType(Bar::clazz));
    }

    public function testRemoveBindingTypeIgnoresUnknownTypes()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->removeBindingType(Bar::clazz);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertTrue($discovery->hasBindingType(Foo::clazz));
        $this->assertFalse($discovery->hasBindingType(Bar::clazz));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testRemoveBindingTypeFailsIfInvalidType()
    {
        $discovery = $this->createDiscovery();
        $discovery->removeBindingType(new stdClass());
    }

    public function testRemoveBindingTypeRemovesCorrespondingBindings()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBindingType(new BindingType(Bar::clazz, self::RESOURCE_BINDING));
        $discovery->addBinding($binding1 = new ResourceBinding('/path1', Foo::clazz));
        $discovery->addBinding($binding2 = new ResourceBinding('/path2', Foo::clazz));
        $discovery->addBinding($binding3 = new ResourceBinding('/path3', Bar::clazz));

        $discovery->removeBindingType(Foo::clazz);

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertEquals(array($binding3), $discovery->getBindings());
    }

    public function testRemoveBindingTypes()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBindingType(new BindingType(Bar::clazz, self::RESOURCE_BINDING));
        $discovery->removeBindingTypes();

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertEquals(array(), $discovery->getBindingTypes());
        $this->assertFalse($discovery->hasBindingType(Foo::clazz));
        $this->assertFalse($discovery->hasBindingType(Bar::clazz));
    }

    public function testRemoveBindingTypesRemovesBindings()
    {
        $discovery = $this->createDiscovery();
        $discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));
        $discovery->addBindingType(new BindingType(Bar::clazz, self::RESOURCE_BINDING));
        $discovery->addBinding($binding1 = new ResourceBinding('/path1', Foo::clazz));
        $discovery->addBinding($binding2 = new ResourceBinding('/path2', Bar::clazz));
        $discovery->removeBindingTypes();

        $discovery = $this->loadDiscoveryFromStorage($discovery);

        $this->assertCount(0, $discovery->getBindings());
    }
}
