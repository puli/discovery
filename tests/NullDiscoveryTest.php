<?php


/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests;

use PHPUnit_Framework_TestCase;
use Puli\Discovery\NullDiscovery;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullDiscoveryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NullDiscovery
     */
    private $discovery;

    protected function setUp()
    {
        $this->discovery = new NullDiscovery();
    }

    public function testBind()
    {
        $this->discovery->defineType('type');
        $this->discovery->bind('/path', 'type');

        $this->assertFalse($this->discovery->isTypeDefined('type'));
        $this->assertCount(0, $this->discovery->find('type'));
        $this->assertCount(0, $this->discovery->getBindings());
    }

    public function testUnbind()
    {
        $this->assertCount(0, $this->discovery->getBindings());

        $this->discovery->unbind('/path');

        $this->assertFalse($this->discovery->isTypeDefined('type'));
        $this->assertCount(0, $this->discovery->find('type'));
        $this->assertCount(0, $this->discovery->getBindings());
    }

    public function testUndefineType()
    {
        $this->assertFalse($this->discovery->isTypeDefined('type'));

        $this->discovery->undefineType('type');

        $this->assertFalse($this->discovery->isTypeDefined('type'));
    }

    /**
     * @expectedException \Puli\Discovery\Api\NoSuchTypeException
     */
    public function testGetDefinedTypeAlwaysThrowsException()
    {
        $this->discovery->defineType('type');

        $this->discovery->getDefinedType('type');
    }
}
