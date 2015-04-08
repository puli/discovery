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
use Puli\Discovery\Api\Binding\BindingParameter;
use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Binding\AbstractBinding;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractBindingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string      $query
     * @param BindingType $type
     * @param array       $parameters
     * @param string      $language
     *
     * @return AbstractBinding
     */
    abstract protected function createBinding($query, BindingType $type, array $parameters = array(), $language = 'glob');

    public function testCreate()
    {
        $type = new BindingType('type');

        $binding = $this->createBinding('/path/*', $type);

        $this->assertSame('/path/*', $binding->getQuery());
        $this->assertSame($type, $binding->getType());
        $this->assertSame(array(), $binding->getParameterValues());
        $this->assertFalse($binding->hasParameterValue('param'));
    }

    public function testCreateWithParameters()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        ));

        $binding = $this->createBinding('/path/*', $type, array(
            'param1' => 'value',
        ));

        $this->assertSame($type, $binding->getType());
        $this->assertSame(array(
            'param1' => 'value',
            'param2' => null,
        ), $binding->getParameterValues());
        $this->assertTrue($binding->hasParameterValue('param1'));
        $this->assertTrue($binding->hasParameterValue('param2'));
        $this->assertFalse($binding->hasParameterValue('foo'));
        $this->assertSame('value', $binding->getParameterValue('param1'));
        $this->assertNull($binding->getParameterValue('param2'));
    }

    public function testCreateWithParameterDefaults()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param', BindingParameter::OPTIONAL, 'default'),
        ));

        $binding = $this->createBinding('/path/*', $type);

        $this->assertSame($type, $binding->getType());
        $this->assertSame(array('param' => 'default'), $binding->getParameterValues());
        $this->assertTrue($binding->hasParameterValue('param'));
        $this->assertSame('default', $binding->getParameterValue('param'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Binding\MissingParameterException
     * @expectedExceptionMessage param
     */
    public function testCreateFailsIfMissingRequiredParameter()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param', BindingParameter::REQUIRED),
        ));

        $this->createBinding('/file1', $type);
    }

    /**
     * @expectedException \Puli\Discovery\Api\Binding\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testCreateFailsIfUnknownParameter()
    {
        $type = new BindingType('type');

        $this->createBinding('/file1', $type, array(
            'foo' => 'bar',
        ));
    }

    /**
     * @expectedException \Puli\Discovery\Api\Binding\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testGetParameterFailsIfNotFound()
    {
        $type = new BindingType('type');

        $binding = $this->createBinding('/file1', $type);

        $binding->getParameterValue('foo');
    }

    public function testEqual()
    {
        $type = new BindingType('type');

        $binding1 = $this->createBinding('/path', $type);
        $binding2 = $this->createBinding('/path', $type);

        $this->assertTrue($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentTypeInstance()
    {
        $type1 = new BindingType('type');
        $type2 = new BindingType('type');

        $binding1 = $this->createBinding('/path', $type1);
        $binding2 = $this->createBinding('/path', $type2);

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentPath()
    {
        $type = new BindingType('type');

        $binding1 = $this->createBinding('/path1', $type);
        $binding2 = $this->createBinding('/path2', $type);

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentParameters()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param'),
        ));

        $binding1 = $this->createBinding('/path', $type, array('param' => 'foo'));
        $binding2 = $this->createBinding('/path', $type, array('param' => 'bar'));

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentParameterTypes()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param'),
        ));

        $binding1 = $this->createBinding('/path', $type, array('param' => '2'));
        $binding2 = $this->createBinding('/path', $type, array('param' => 2));

        $this->assertFalse($binding1->equals($binding2));
    }

    public function testEqualIfDifferentParameterOrder()
    {
        $type = new BindingType('type', array(
            new BindingParameter('foo'),
            new BindingParameter('bar'),
        ));

        $binding1 = $this->createBinding('/path', $type, array('foo' => 'bar', 'bar' => 'foo'));
        $binding2 = $this->createBinding('/path', $type, array('bar' => 'foo', 'foo' => 'bar'));

        $this->assertTrue($binding1->equals($binding2));
    }

    public function testEqualIfDefaultValues()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param', BindingParameter::OPTIONAL, 'default'),
        ));

        $binding1 = $this->createBinding('/path', $type, array('param' => 'default'));
        $binding2 = $this->createBinding('/path', $type);

        $this->assertTrue($binding1->equals($binding2));
    }

    public function testNotEqualIfDifferentLanguage()
    {
        $type = new BindingType('type', array(
            new BindingParameter('param'),
        ));

        $binding1 = $this->createBinding('/path', $type, array(), 'glob');
        $binding2 = $this->createBinding('/path', $type, array(), 'xpath');

        $this->assertFalse($binding1->equals($binding2));
    }
}
