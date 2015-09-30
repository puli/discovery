<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests\Api\Type;

use PHPUnit_Framework_TestCase;
use Puli\Discovery\Api\Type\BindingParameter;
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Tests\Fixtures\Foo;
use stdClass;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingTypeTest extends PHPUnit_Framework_TestCase
{
    const RESOURCE_BINDING = 'Puli\Discovery\Binding\ResourceBinding';

    const SUB_RESOURCE_BINDING = 'Puli\Discovery\Tests\Fixtures\SubResourceBinding';

    const CLASS_BINDING = 'Puli\Discovery\Binding\ClassBinding';

    public function testSetParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            $param1 = new BindingParameter('param1'),
            $param2 = new BindingParameter('param2'),
        ));

        $this->assertSame(array(
            'param1' => $param1,
            'param2' => $param2,
        ), $type->getParameters());
        $this->assertTrue($type->hasParameter('param1'));
        $this->assertFalse($type->hasParameter('foo'));
        $this->assertSame($param1, $type->getParameter('param1'));
        $this->assertTrue($type->hasParameters());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfInvalidParameter()
    {
        new BindingType(Foo::clazz, array(new \stdClass()));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchParameterException
     */
    public function testGetParameterFailsIfNotSet()
    {
        $type = new BindingType(Foo::clazz);

        $type->getParameter('foo');
    }

    public function testHasNoParameters()
    {
        $type = new BindingType(Foo::clazz);

        $this->assertFalse($type->hasParameters());
    }

    public function testHasRequiredParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::OPTIONAL),
            new BindingParameter('param2', BindingParameter::REQUIRED),
        ));

        $this->assertTrue($type->hasRequiredParameters());
    }

    public function testHasNoRequiredParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::OPTIONAL),
            new BindingParameter('param2', BindingParameter::OPTIONAL),
        ));

        $this->assertFalse($type->hasRequiredParameters());
    }

    public function testHasOptionalParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::OPTIONAL),
            new BindingParameter('param2', BindingParameter::REQUIRED),
        ));

        $this->assertTrue($type->hasOptionalParameters());
    }

    public function testHasNoOptionalParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::REQUIRED),
            new BindingParameter('param2', BindingParameter::REQUIRED),
        ));

        $this->assertFalse($type->hasOptionalParameters());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfInvalidTypeName()
    {
        new BindingType(new stdClass());
    }

    public function testGetParameterValues()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param', BindingParameter::OPTIONAL, 'default'),
        ));

        $this->assertSame(array('param' => 'default'), $type->getParameterValues());
    }

    public function testGetParameterValuesDoesNotIncludeRequiredParameters()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param', BindingParameter::REQUIRED),
        ));

        $this->assertSame(array(), $type->getParameterValues());
    }

    public function testHasParameterValues()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param', BindingParameter::OPTIONAL, 'default'),
        ));

        $this->assertTrue($type->hasParameterValues());
    }

    public function testHasNoParameterValues()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param', BindingParameter::REQUIRED),
        ));

        $this->assertFalse($type->hasParameterValues());
    }

    public function testGetParameterValue()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param', BindingParameter::OPTIONAL, 'default'),
        ));

        $this->assertSame('default', $type->getParameterValue('param'));
    }

    public function testGetParameterValueReturnsNullForRequired()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param', BindingParameter::REQUIRED),
        ));

        $this->assertNull($type->getParameterValue('param'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchParameterException
     */
    public function testGetParameterValueFailsIfNotSet()
    {
        $type = new BindingType(Foo::clazz);

        $type->getParameterValue('foo');
    }

    public function testHasParameterValue()
    {
        $type = new BindingType(Foo::clazz, array(
            new BindingParameter('param1', BindingParameter::OPTIONAL, 'default'),
            new BindingParameter('param2', BindingParameter::REQUIRED),
        ));

        $this->assertTrue($type->hasParameterValue('param1'));
        $this->assertFalse($type->hasParameterValue('param2'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchParameterException
     */
    public function testHasParameterValueFailsIfNotSet()
    {
        $type = new BindingType(Foo::clazz);

        $type->hasParameterValue('foo');
    }

    public function testRestrictBindingClasses()
    {
        $type = new BindingType(Foo::clazz, array(), array(self::RESOURCE_BINDING));

        $this->assertTrue($type->acceptsBinding(self::RESOURCE_BINDING));
        $this->assertTrue($type->acceptsBinding(self::SUB_RESOURCE_BINDING));
        $this->assertFalse($type->acceptsBinding(self::CLASS_BINDING));
        $this->assertSame(array(self::RESOURCE_BINDING), $type->getAcceptedBindings());
    }

    public function testUnrestrictedBindingClasses()
    {
        $type = new BindingType(Foo::clazz);

        $this->assertTrue($type->acceptsBinding(self::RESOURCE_BINDING));
        $this->assertTrue($type->acceptsBinding(self::CLASS_BINDING));
        $this->assertSame(array(), $type->getAcceptedBindings());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfBindingClassNoClassOrInterface()
    {
        new BindingType(Foo::clazz, array(), array(__NAMESPACE__.'\\Foobar'));
    }
}
