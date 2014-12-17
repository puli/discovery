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
use Puli\Discovery\Binding\BindingParameter;

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

    public function testIsOptionalByDefault()
    {
        $param = new BindingParameter('name');

        $this->assertTrue($param->isOptional());
        $this->assertFalse($param->isRequired());
    }

    public function testIsNotOptionalIfRequired()
    {
        $param = new BindingParameter('name', BindingParameter::REQUIRED);

        $this->assertFalse($param->isOptional());
        $this->assertTrue($param->isRequired());
    }

    public function testSetDefaultValue()
    {
        $param = new BindingParameter('name', null, 'default');

        $this->assertSame('name', $param->getName());
        $this->assertSame('default', $param->getDefaultValue());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFailIfRequiredParameterHasDefault()
    {
        new BindingParameter('name', BindingParameter::REQUIRED, 'default');
    }
}
