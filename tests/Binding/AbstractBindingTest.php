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
use Puli\Discovery\Api\BindingParameter;
use Puli\Discovery\Api\BindingType;
use Puli\Discovery\Binding\AbstractBinding;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractBindingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string      $language
     * @param string      $query
     * @param BindingType $type
     * @param array       $parameters
     *
     * @return AbstractBinding
     */
    abstract protected function createBinding($query, $language, BindingType $type, array $parameters = array());

    public function testCreate()
    {
        $type = new BindingType('type');

        $binding = $this->createBinding('/path/*', 'glob', $type);

        $this->assertSame('/path/*', $binding->getQuery());
        $this->assertSame($type, $binding->getType());
        $this->assertSame(array(), $binding->getParameters());
        $this->assertFalse($binding->hasParameter('param'));
    }

    public function testCreateWithParameters()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        ));

        $binding = $this->createBinding('/path/*', 'glob', $type, array(
            'param1' => 'value',
        ));

        $this->assertSame($type, $binding->getType());
        $this->assertSame(array(
            'param1' => 'value',
            'param2' => null,
        ), $binding->getParameters());
        $this->assertTrue($binding->hasParameter('param1'));
        $this->assertTrue($binding->hasParameter('param2'));
        $this->assertFalse($binding->hasParameter('foo'));
        $this->assertSame('value', $binding->getParameter('param1'));
        $this->assertNull($binding->getParameter('param2'));
    }

    public function testCreateWithParameterDefaults()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param', false, 'default'),
        ));

        $binding = $this->createBinding('/path/*', 'glob', $type);

        $this->assertSame($type, $binding->getType());
        $this->assertSame(array('param' => 'default'), $binding->getParameters());
        $this->assertTrue($binding->hasParameter('param'));
        $this->assertSame('default', $binding->getParameter('param'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\MissingParameterException
     * @expectedExceptionMessage param
     */
    public function testCreateFailsIfMissingRequiredParameter()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param', true),
        ));

        $this->createBinding('/file1', 'glob', $type);
    }

    /**
     * @expectedException \Puli\Discovery\Api\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testCreateFailsIfUnknownParameter()
    {
        $type = new BindingType('type');

        $this->createBinding('/file1', 'glob', $type, array(
            'foo' => 'bar',
        ));
    }

    /**
     * @expectedException \Puli\Discovery\Api\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testGetParameterFailsIfNotFound()
    {
        $type = new BindingType('type');

        $binding = $this->createBinding('/file1', 'glob', $type);

        $binding->getParameter('foo');
    }

    public function testEqual()
    {
        $type = new BindingType('type');

        $binding1 = $this->createBinding('/path', 'glob', $type);
        $binding2 = $this->createBinding('/path', 'glob', $type);

        $this->assertTrue($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentTypeInstance()
    {
        $type1 = new BindingType('type');
        $type2 = new BindingType('type');

        $binding1 = $this->createBinding('/path', 'glob', $type1);
        $binding2 = $this->createBinding('/path', 'glob', $type2);

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentPath()
    {
        $type = new BindingType('type');

        $binding1 = $this->createBinding('/path1', 'glob', $type);
        $binding2 = $this->createBinding('/path2', 'glob', $type);

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentParameters()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param'),
        ));

        $binding1 = $this->createBinding('/path', 'glob', $type, array('param' => 'foo'));
        $binding2 = $this->createBinding('/path', 'glob', $type, array('param' => 'bar'));

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentParameterTypes()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param'),
        ));

        $binding1 = $this->createBinding('/path', 'glob', $type, array('param' => '2'));
        $binding2 = $this->createBinding('/path', 'glob', $type, array('param' => 2));

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testEqualIfDifferentParameterOrder()
    {
        $type = new BindingType('type', array(
            new BindingParameter('foo'),
            new BindingParameter('bar'),
        ));

        $binding1 = $this->createBinding('/path', 'glob', $type, array('foo' => 'bar', 'bar' => 'foo'));
        $binding2 = $this->createBinding('/path', 'glob', $type, array('bar' => 'foo', 'foo' => 'bar'));

        $this->assertTrue($binding1->equals($binding2));
    }

    public function testEqualIfDefaultValues()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param', false, 'default'),
        ));

        $binding1 = $this->createBinding('/path', 'glob', $type, array('param' => 'default'));
        $binding2 = $this->createBinding('/path', 'glob', $type);

        $this->assertTrue($binding1->equals($binding2));
    }
}
