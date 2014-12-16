<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discover\Tests\Binding;

use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingTypeTest extends \PHPUnit_Framework_TestCase
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
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfInvalidParameter()
    {
        new BindingType('name', array(new \stdClass()));
    }
}
