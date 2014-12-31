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

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BindingParameterTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $param = new BindingParameter('name');

        $this->assertSame('name', $param->getName());
        $this->assertNull($param->getDefaultValue());
    }

    public function testIsNotRequiredByDefault()
    {
        $param = new BindingParameter('name');

        $this->assertFalse($param->isRequired());
    }

    public function testRequired()
    {
        $param = new BindingParameter('name', true);

        $this->assertTrue($param->isRequired());
    }

    public function testSetDefaultValue()
    {
        $param = new BindingParameter('name', false, 'default');

        $this->assertSame('name', $param->getName());
        $this->assertSame('default', $param->getDefaultValue());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFailIfRequiredParameterHasDefault()
    {
        new BindingParameter('name', true, 'default');
    }

    public function getValidNames()
    {
        return array(
            array('my-param'),
            array('myParam'),
            array('my_param'),
            array('my123param'),
            array('my/param'),
            array('my@param'),
            array('my:param'),
        );
    }

    /**
     * @dataProvider getValidNames
     */
    public function testValidName($name)
    {
        $descriptor = new BindingParameter($name);

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
        new BindingParameter($name);
    }
}
