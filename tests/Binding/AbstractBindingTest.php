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

use Puli\Discovery\Binding\AbstractBinding;
use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractBindingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string      $path
     * @param BindingType $type
     * @param array       $parameters
     *
     * @return AbstractBinding
     */
    abstract protected function createBinding($path, BindingType $type, array $parameters = array());

    public function testCreate()
    {
        $type = new BindingType('type');

        $binding = $this->createBinding('/path/*', $type);

        $this->assertSame('/path/*', $binding->getPath());
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

        $binding = $this->createBinding('/path/*', $type, array(
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
            new BindingParameter('param', null, 'default'),
        ));

        $binding = $this->createBinding('/path/*', $type);

        $this->assertSame($type, $binding->getType());
        $this->assertSame(array('param' => 'default'), $binding->getParameters());
        $this->assertTrue($binding->hasParameter('param'));
        $this->assertSame('default', $binding->getParameter('param'));
    }

    /**
     * @expectedException \Puli\Discovery\Binding\MissingParameterException
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
     * @expectedException \Puli\Discovery\Binding\NoSuchParameterException
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
     * @expectedException \Puli\Discovery\Binding\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testGetParameterFailsIfNotFound()
    {
        $type = new BindingType('type');

        $binding = $this->createBinding('/file1', $type);

        $binding->getParameter('foo');
    }
}
