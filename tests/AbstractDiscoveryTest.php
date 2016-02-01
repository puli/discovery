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

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Puli\Discovery\Api\Binding\Initializer\BindingInitializer;
use Puli\Discovery\Api\Discovery;
use Puli\Discovery\Api\Type\BindingParameter;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\ClassBinding;
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
abstract class AbstractDiscoveryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|BindingInitializer
     */
    protected $initializer;

    /**
     * @param BindingType[]     $types
     * @param ResourceBinding[] $bindings
     *
     * @return Discovery
     */
    abstract protected function createLoadedDiscovery(array $types = array(), array $bindings = array(), array $initializers = array());

    protected function setUp()
    {
        $this->initializer = $this->getMock('Puli\Discovery\Api\Binding\Initializer\BindingInitializer');
    }

    public function testFindBindings()
    {
        $type1 = new BindingType(Foo::clazz);
        $type2 = new BindingType(Bar::clazz);
        $binding1 = new ResourceBinding('/file1', Foo::clazz);
        $binding2 = new ResourceBinding('/file2', Foo::clazz);
        $binding3 = new ClassBinding(__CLASS__, Bar::clazz);

        $discovery = $this->createLoadedDiscovery(array($type1, $type2), array($binding1, $binding2, $binding3));

        $this->assertEquals(array($binding1, $binding2), $discovery->findBindings(Foo::clazz));
        $this->assertEquals(array($binding3), $discovery->findBindings(Bar::clazz));
    }

    public function testFindBindingsWithExpression()
    {
        $type1 = new BindingType(Foo::clazz, array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        ));
        $type2 = new BindingType(Bar::clazz, array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        ));
        $binding1 = new ResourceBinding('/file1', Foo::clazz, array('param1' => 'value1', 'param2' => 'value2'));
        $binding2 = new ResourceBinding('/file2', Foo::clazz, array('param1' => 'value1'));
        $binding3 = new ClassBinding(__CLASS__, Bar::clazz, array('param1' => 'value1', 'param2' => 'value2'));

        $discovery = $this->createLoadedDiscovery(array($type1, $type2), array($binding1, $binding2, $binding3));

        $exprParam1 = Expr::method('getParameterValue', 'param1', Expr::same('value1'));
        $exprParam2 = Expr::method('getParameterValue', 'param1', Expr::same('value1'))
            ->andMethod('getParameterValue', 'param2', Expr::same('value2'));

        $this->assertEquals(array($binding1, $binding2), $discovery->findBindings(Foo::clazz, $exprParam1));
        $this->assertEquals(array($binding1), $discovery->findBindings(Foo::clazz, $exprParam2));
        $this->assertEquals(array($binding3), $discovery->findBindings(Bar::clazz, $exprParam2));
    }

    public function testFindBindingsReturnsEmptyArrayIfUnknownType()
    {
        $discovery = $this->createLoadedDiscovery();

        $this->assertEquals(array(), $discovery->findBindings(Foo::clazz));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testFindBindingsFailsIfInvalidType()
    {
        $discovery = $this->createLoadedDiscovery();
        $discovery->findBindings(new stdClass());
    }

    public function testGetBindings()
    {
        $type1 = new BindingType(Foo::clazz);
        $type2 = new BindingType(Bar::clazz);
        $binding1 = new ResourceBinding('/file1', Foo::clazz);
        $binding2 = new ResourceBinding('/file2', Foo::clazz);
        $binding3 = new ClassBinding(__CLASS__, Bar::clazz);

        $discovery = $this->createLoadedDiscovery(array($type1, $type2), array($binding1, $binding2, $binding3));

        $this->assertEquals(array($binding1, $binding2, $binding3), $discovery->getBindings());
    }

    public function testGetNoBindings()
    {
        $discovery = $this->createLoadedDiscovery();

        $this->assertEquals(array(), $discovery->getBindings());
    }

    public function testHasBindings()
    {
        $type = new BindingType(Foo::clazz);
        $binding = new ResourceBinding('/file1', Foo::clazz);

        $discovery = $this->createLoadedDiscovery(array($type), array($binding));

        $this->assertTrue($discovery->hasBindings());
    }

    public function testHasNoBindings()
    {
        $type = new BindingType(Foo::clazz);

        $discovery = $this->createLoadedDiscovery(array($type));

        $this->assertFalse($discovery->hasBindings());
    }

    public function testHasBindingsWithType()
    {
        $type1 = new BindingType(Foo::clazz);
        $type2 = new BindingType(Bar::clazz);
        $binding = new ResourceBinding('/file1', Foo::clazz);

        $discovery = $this->createLoadedDiscovery(array($type1, $type2), array($binding));

        $this->assertTrue($discovery->hasBindings(Foo::clazz));
        $this->assertFalse($discovery->hasBindings(Bar::clazz));
    }

    public function testHasBindingsWithTypeReturnsFalseIfUnknownType()
    {
        $discovery = $this->createLoadedDiscovery();
        $this->assertFalse($discovery->hasBindings(Foo::clazz));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testHasBindingsWithTypeFailsIfInvalidType()
    {
        $discovery = $this->createLoadedDiscovery();
        $discovery->hasBindings(new stdClass());
    }

    public function testHasBindingsWithTypeAndExpression()
    {
        $type1 = new BindingType(Foo::clazz, array(
            new BindingParameter('param'),
        ));
        $type2 = new BindingType(Bar::clazz);
        $binding = new ResourceBinding('/file1', Foo::clazz, array('param' => 'foo'));

        $discovery = $this->createLoadedDiscovery(array($type1, $type2), array($binding));

        $this->assertTrue($discovery->hasBindings(Foo::clazz, Expr::method('getParameterValue', 'param', Expr::same('foo'))));
        $this->assertFalse($discovery->hasBindings(Foo::clazz, Expr::method('getParameterValue', 'param', Expr::same('bar'))));
    }

    public function testHasBindingsWithTypeAndExpressionReturnsFalseIfUnknownType()
    {
        $discovery = $this->createLoadedDiscovery();
        $this->assertFalse($discovery->hasBindings(Foo::clazz, Expr::method('getParameterValue', 'param', Expr::same('foo'))));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testHasBindingsWithTypeAndParametersFailsIfInvalidType()
    {
        $discovery = $this->createLoadedDiscovery();
        $discovery->hasBindings(new stdClass(), Expr::method('getParameterValue', 'param', Expr::same('foo')));
    }

    public function testGetBindingType()
    {
        $type1 = new BindingType(Foo::clazz);
        $type2 = new BindingType(Bar::clazz);

        $discovery = $this->createLoadedDiscovery(array($type1, $type2));

        $this->assertEquals($type1, $discovery->getBindingType(Foo::clazz));
        $this->assertEquals($type2, $discovery->getBindingType(Bar::clazz));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchTypeException
     * @expectedExceptionMessage Foo
     */
    public function testGetBindingTypeFailsIfUnknownType()
    {
        $discovery = $this->createLoadedDiscovery();

        $discovery->getBindingType(Foo::clazz);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testGetBindingTypeFailsIfInvalidType()
    {
        $discovery = $this->createLoadedDiscovery();
        $discovery->getBindingType(new stdClass());
    }

    public function testGetBindingTypes()
    {
        $type1 = new BindingType(Foo::clazz);
        $type2 = new BindingType(Bar::clazz);

        $discovery = $this->createLoadedDiscovery(array($type1, $type2));

        $this->assertEquals(array($type1, $type2), $discovery->getBindingTypes());
    }

    public function testHasBindingType()
    {
        $type = new BindingType(Foo::clazz);

        $discovery = $this->createLoadedDiscovery(array($type));

        $this->assertTrue($discovery->hasBindingType(Foo::clazz));
        $this->assertFalse($discovery->hasBindingType(Bar::clazz));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testHasBindingTypeFailsIfInvalidType()
    {
        $discovery = $this->createLoadedDiscovery();
        $discovery->hasBindingType(new stdClass());
    }
}
