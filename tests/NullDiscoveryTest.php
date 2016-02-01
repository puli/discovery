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
use Puli\Discovery\Api\Type\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\NullDiscovery;
use Puli\Discovery\Tests\Fixtures\Foo;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullDiscoveryTest extends PHPUnit_Framework_TestCase
{
    const RESOURCE_BINDING = 'Puli\Discovery\Binding\ResourceBinding';

    /**
     * @var NullDiscovery
     */
    private $discovery;

    protected function setUp()
    {
        $this->discovery = new NullDiscovery();
    }

    public function testFindBindings()
    {
        $this->discovery->addBinding(new ResourceBinding('/path', Foo::clazz));

        $this->assertSame(array(), $this->discovery->findBindings(Foo::clazz));
    }

    public function testGetBindings()
    {
        $this->discovery->addBinding(new ResourceBinding('/path', Foo::clazz));

        $this->assertSame(array(), $this->discovery->getBindings());
    }

    public function testHasBindings()
    {
        $this->discovery->addBinding(new ResourceBinding('/path', Foo::clazz));

        $this->assertFalse($this->discovery->hasBindings());
    }

    public function testGetBindingTypes()
    {
        $this->discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));

        $this->assertSame(array(), $this->discovery->getBindingTypes());
    }

    public function testHasBindingTypes()
    {
        $this->discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));

        $this->assertFalse($this->discovery->hasBindingTypes());
    }

    /**
     * @expectedException \Puli\Discovery\Api\Type\NoSuchTypeException
     */
    public function testGetBindingType()
    {
        $this->discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));

        $this->discovery->getBindingType(Foo::clazz);
    }

    public function testHasBindingType()
    {
        $this->discovery->addBindingType(new BindingType(Foo::clazz, self::RESOURCE_BINDING));

        $this->assertFalse($this->discovery->hasBindingType(Foo::clazz));
    }
}
