<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests\Binding;

use PHPUnit_Framework_TestCase;
use Puli\Discovery\Api\Type\BindingParameter;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\AbstractBinding;
use Puli\Discovery\Tests\Fixtures\Bar;
use Puli\Discovery\Tests\Fixtures\Foo;
use Rhumsaa\Uuid\Uuid;
use stdClass;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractBindingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $typeName
     * @param array  $parameterValues
     * @param Uuid   $uuid
     *
     * @return AbstractBinding
     */
    abstract protected function createBinding($typeName, array $parameterValues = array(), Uuid $uuid = null);

    public function testCreate()
    {
        $binding = $this->createBinding(Foo::clazz);

        $this->assertSame(Foo::clazz, $binding->getTypeName());
        $this->assertSame(array(), $binding->getParameterValues());
        $this->assertFalse($binding->hasParameterValue('param'));
        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $binding->getUuid());
    }

    public function testCreateWithUuid()
    {
        $uuid = Uuid::uuid4();
        $binding = $this->createBinding(Foo::clazz, array(), $uuid);

        $this->assertSame($uuid, $binding->getUuid());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testCreateFailsIfInvalidType()
    {
        $this->createBinding(new stdClass());
    }

    public function testCreateWithParameters()
    {
        $binding = $this->createBinding(Foo::clazz, array(
            'param1' => 'value',
        ));

        $this->assertSame(Foo::clazz, $binding->getTypeName());
        $this->assertSame(array(
            'param1' => 'value',
        ), $binding->getParameterValues());
        $this->assertTrue($binding->hasParameterValue('param1'));
        $this->assertFalse($binding->hasParameterValue('foo'));
        $this->assertSame('value', $binding->getParameterValue('param1'));
    }

    public function testInitialize()
    {
        $type = new BindingType(Foo::clazz);
        $binding = $this->createBinding(Foo::clazz);

        $this->assertFalse($binding->isInitialized());

        $binding->initialize($type);

        $this->assertSame($type, $binding->getType());
        $this->assertTrue($binding->isInitialized());
    }

    public function testInitializeWithParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        ));

        $binding = $this->createBinding(Foo::clazz, array(
            'param1' => 'value',
        ));

        $this->assertSame(array('param1' => 'value'), $binding->getParameterValues());
        $this->assertTrue($binding->hasParameterValue('param1'));
        $this->assertFalse($binding->hasParameterValue('param2'));
        $this->assertFalse($binding->hasParameterValue('foo'));
        $this->assertSame('value', $binding->getParameterValue('param1'));

        $binding->initialize($type);

        $this->assertSame(array('param1' => 'value', 'param2' => null), $binding->getParameterValues());
        $this->assertTrue($binding->hasParameterValue('param1'));
        $this->assertTrue($binding->hasParameterValue('param2'));
        $this->assertFalse($binding->hasParameterValue('foo'));
        $this->assertSame('value', $binding->getParameterValue('param1'));
        $this->assertNull($binding->getParameterValue('param2'));

        // exclude default values
        $this->assertSame(array('param1' => 'value'), $binding->getParameterValues(false));
        $this->assertTrue($binding->hasParameterValue('param1', false));
        $this->assertFalse($binding->hasParameterValue('param2', false));
        $this->assertFalse($binding->hasParameterValue('foo', false));
        $this->assertSame('value', $binding->getParameterValue('param1', false));
    }

    public function testInitializeWithParameterDefaults()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::OPTIONAL, 'default'),
            new BindingParameter('param2'),
        ));

        $binding = $this->createBinding(Foo::clazz, array(
            'param2' => 'value',
        ));

        $this->assertSame(array('param2' => 'value'), $binding->getParameterValues());
        $this->assertFalse($binding->hasParameterValue('param1'));
        $this->assertTrue($binding->hasParameterValue('param2'));
        $this->assertSame('value', $binding->getParameterValue('param2'));

        $binding->initialize($type);

        $this->assertSame(array('param1' => 'default', 'param2' => 'value'), $binding->getParameterValues());
        $this->assertTrue($binding->hasParameterValue('param1'));
        $this->assertTrue($binding->hasParameterValue('param2'));
        $this->assertSame('default', $binding->getParameterValue('param1'));
        $this->assertSame('value', $binding->getParameterValue('param2'));

        // exclude default values
        $this->assertSame(array('param2' => 'value'), $binding->getParameterValues(false));
        $this->assertFalse($binding->hasParameterValue('param1', false));
        $this->assertTrue($binding->hasParameterValue('param2', false));
        $this->assertSame('value', $binding->getParameterValue('param2', false));
    }

    public function testInitializeWithRequiredParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::OPTIONAL, 'default'),
            new BindingParameter('param2', BindingParameter::REQUIRED),
        ));

        $binding = $this->createBinding(Foo::clazz, array(
            'param2' => 'value',
        ));

        $this->assertSame(array('param2' => 'value'), $binding->getParameterValues());
        $this->assertFalse($binding->hasParameterValue('param1'));
        $this->assertTrue($binding->hasParameterValue('param2'));
        $this->assertSame('value', $binding->getParameterValue('param2'));

        $binding->initialize($type);

        $this->assertSame(array('param1' => 'default', 'param2' => 'value'), $binding->getParameterValues());
        $this->assertTrue($binding->hasParameterValue('param1'));
        $this->assertTrue($binding->hasParameterValue('param2'));
        $this->assertSame('default', $binding->getParameterValue('param1'));
        $this->assertSame('value', $binding->getParameterValue('param2'));

        // exclude default values
        $this->assertSame(array('param2' => 'value'), $binding->getParameterValues(false));
        $this->assertFalse($binding->hasParameterValue('param1', false));
        $this->assertTrue($binding->hasParameterValue('param2', false));
        $this->assertSame('value', $binding->getParameterValue('param2', false));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\MissingParameterException
     * @expectedExceptionMessage param
     */
    public function testInitializeFailsIfMissingRequiredParameter()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param', BindingParameter::REQUIRED),
        ));

        $binding = $this->createBinding(Foo::clazz);

        $binding->initialize($type);
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testInitializeFailsIfUnknownParameter()
    {
        $type = new BindingType(Foo::clazz);

        $binding = $this->createBinding(Foo::clazz, array(
            'foo' => 'bar',
        ));

        $binding->initialize($type);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Bar
     */
    public function testInitializeFailsIfWrongType()
    {
        $type = new BindingType(Foo::clazz);

        $binding = $this->createBinding(Bar::clazz);

        $binding->initialize($type);
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\BindingNotAcceptedException
     */
    public function testInitializeFailsIfBindingNotAccepted()
    {
        $binding = $this->getMock('Puli\Discovery\Api\Binding\Binding');
        $type = new BindingType(Foo::clazz, array(), array(get_class($binding)));

        $binding = $this->createBinding(Foo::clazz);

        $binding->initialize($type);
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testGetParameterFailsIfNotFound()
    {
        $binding = $this->createBinding(Foo::clazz);

        $binding->getParameterValue('foo');
    }

    /**
     * @expectedException \Puli\Discovery\Api\Binding\Initializer\NotInitializedException
     */
    public function testGetTypeFailsIfNotInitialized()
    {
        $binding = $this->createBinding(Foo::clazz);

        $binding->getType();
    }

    public function testSerialize()
    {
        $binding = $this->createBinding(Foo::clazz, array(
            'param1' => 'value',
        ));

        $unserialized = unserialize(serialize($binding));

        $this->assertEquals($binding, $unserialized);
    }

    public function testSerializeInitialized()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        ));

        $binding = $this->createBinding(Foo::clazz, array(
            'param1' => 'value',
        ));
        $binding->initialize($type);

        $unserialized = unserialize(serialize($binding));
        $unserialized->initialize($type);

        $this->assertEquals($binding, $unserialized);
    }
}
