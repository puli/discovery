<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests\Api\Binding;

use PHPUnit_Framework_TestCase;
use Puli\Discovery\Api\Binding\BindingParameter;
use Puli\Discovery\Api\Binding\BindingType;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingTypeTest extends PHPUnit_Framework_TestCase
{
    public function testSetParameters()
    {
        $type = new BindingType('name', array(
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
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfInvalidParameter()
    {
        new BindingType('name', array(new \stdClass()));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Binding\NoSuchParameterException
     */
    public function testGetParameterFailsIfNotSet()
    {
        $type = new BindingType('name');

        $type->getParameter('foo');
    }

    public function getValidNames()
    {
        return array(
            array('my-type'),
            array('myTypeName'),
            array('my_type_name'),
            array('my123Type'),
            array('my/type'),
            array('my@type'),
            array('my:type'),
        );
    }

    /**
     * @dataProvider getValidNames
     */
    public function testValidName($name)
    {
        $descriptor = new BindingType($name);

        $this->assertSame($name, $descriptor->getName());
    }

    public function getInvalidNames()
    {
        return array(
            array(1234),
            array(''),
            array('123digits-first'),
            array('@special-char-first'),
        );
    }

    /**
     * @dataProvider getInvalidNames
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfInvalidName($name)
    {
        new BindingType($name);
    }

    public function testGetParameterValues()
    {
        $type = new BindingType('name', array(
            new BindingParameter('param', false, 'default'),
        ));

        $this->assertSame(array('param' => 'default'), $type->getParameterValues());
    }

    public function testGetParameterValuesDoesNotIncludeRequiredParameters()
    {
        $type = new BindingType('name', array(
            new BindingParameter('param', true),
        ));

        $this->assertSame(array(), $type->getParameterValues());
    }

    public function testGetParameterValue()
    {
        $type = new BindingType('name', array(
            new BindingParameter('param', false, 'default'),
        ));

        $this->assertSame('default', $type->getParameterValue('param'));
    }

    public function testGetParameterValueReturnsNullForRequired()
    {
        $type = new BindingType('name', array(
            new BindingParameter('param', true),
        ));

        $this->assertNull($type->getParameterValue('param'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Binding\NoSuchParameterException
     */
    public function testGetParameterValueFailsIfNotSet()
    {
        $type = new BindingType('name');

        $type->getParameterValue('foo');
    }
}
