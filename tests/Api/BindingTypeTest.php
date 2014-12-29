<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests\Api;

use PHPUnit_Framework_TestCase;
use Puli\Discovery\Api\BindingParameter;
use Puli\Discovery\Api\BindingType;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingTypeTest extends PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $type = new BindingType('name');

        $this->assertSame('name', $type->getName());
    }

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
     * @expectedException \Puli\Discovery\Api\NoSuchParameterException
     */
    public function testGetParameterFailsIfNotSet()
    {
        $type = new BindingType('name');

        $type->getParameter('foo');
    }
}
