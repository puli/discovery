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

use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Repository\Resource\Collection\ResourceCollection;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResourceBindingTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $resources = new ResourceCollection(array(
            $first = new TestFile('/file1'),
            new TestFile('/file2'),
        ));
        $type = new BindingType('type');

        $binding = new ResourceBinding($resources, $type);

        $this->assertSame($resources, $binding->getResources());
        $this->assertSame($first, $binding->getResource());
        $this->assertSame($type, $binding->getType());
        $this->assertSame(array(), $binding->getParameters());
        $this->assertFalse($binding->hasParameter('param'));
    }

    public function testCreateWithParameters()
    {
        $resources = new ResourceCollection(array(
            $first = new TestFile('/file1'),
            new TestFile('/file2'),
        ));
        $type = new BindingType('type', array(
            new BindingParameter('param1'),
            new BindingParameter('param2'),
        ));

        $binding = new ResourceBinding($resources, $type, array(
            'param1' => 'value',
        ));

        $this->assertSame($resources, $binding->getResources());
        $this->assertSame($first, $binding->getResource());
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
        $resources = new ResourceCollection(array(
            $first = new TestFile('/file1'),
            new TestFile('/file2'),
        ));
        $type = new BindingType('type', array(
            new BindingParameter('param', null, 'default'),
        ));

        $binding = new ResourceBinding($resources, $type);

        $this->assertSame($resources, $binding->getResources());
        $this->assertSame($first, $binding->getResource());
        $this->assertSame($type, $binding->getType());
        $this->assertSame(array('param' => 'default'), $binding->getParameters());
        $this->assertTrue($binding->hasParameter('param'));
        $this->assertSame('default', $binding->getParameter('param'));
    }

    /**
     * @expectedException \Puli\Discovery\Binding\BindingException
     */
    public function testCreateFailsIfNoResources()
    {
        $resources = new ResourceCollection();
        $type = new BindingType('type');

        new ResourceBinding($resources, $type);
    }

    /**
     * @expectedException \Puli\Discovery\Binding\MissingParameterException
     * @expectedExceptionMessage param
     */
    public function testCreateFailsIfMissingRequiredParameter()
    {
        $resources = new ResourceCollection(array(
            new TestFile('/file1'),
        ));
        $type = new BindingType('type', array(
            new BindingParameter('param', BindingParameter::REQUIRED),
        ));

        new ResourceBinding($resources, $type);
    }

    /**
     * @expectedException \Puli\Discovery\Binding\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testCreateFailsIfUnknownParameter()
    {
        $resources = new ResourceCollection(array(
            new TestFile('/file1'),
        ));
        $type = new BindingType('type');

        new ResourceBinding($resources, $type, array(
            'foo' => 'bar',
        ));
    }

    /**
     * @expectedException \Puli\Discovery\Binding\NoSuchParameterException
     * @expectedExceptionMessage foo
     */
    public function testGetParameterFailsIfNotFound()
    {
        $resources = new ResourceCollection(array(
            new TestFile('/file1'),
        ));
        $type = new BindingType('type');

        $binding = new ResourceBinding($resources, $type);

        $binding->getParameter('foo');
    }
}
