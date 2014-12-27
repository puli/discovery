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

use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\ManageableDiscovery;
use Puli\Discovery\ResourceDiscovery;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractManageableDiscoveryTest extends AbstractDiscoveryTest
{
    /**
     * @return ManageableDiscovery
     */
    abstract protected function createManageableDiscovery();

    /**
     * @param ResourceBinding[] $bindings
     *
     * @return ResourceDiscovery
     */
    protected function createDiscovery(array $bindings = array())
    {
        foreach ($bindings as $binding) {
            foreach ($binding->getResources() as $resource) {
                $path = $resource->getPath();

                // Prevent duplicate additions
                if (!$this->repo->contains($path) || $resource !== $this->repo->get($path)) {
                    $this->repo->add($path, $resource);
                }
            }
        }

        $discovery = $this->createManageableDiscovery();

        foreach ($bindings as $binding) {
            $type = $binding->getType();

            // Prevent duplicate additions
            if (!$discovery->isDefined($type->getName())) {
                $discovery->define($type);
            }
        }

        foreach ($bindings as $binding) {
            $discovery->bind($binding->getPath(), $binding->getType()->getName(), $binding->getParameters());
        }

        return $discovery;
    }

    /**
     * @expectedException \Puli\Discovery\Binding\BindingException
     * @expectedExceptionMessage /foo
     */
    public function testBindFailsIfResourceNotFound()
    {
        $discovery = $this->createManageableDiscovery();
        $discovery->define('type');

        $discovery->bind('/foo', 'type');
    }

    /**
     * @expectedException \Puli\Discovery\Binding\NoSuchTypeException
     * @expectedExceptionMessage foo
     */
    public function testBindFailsIfTypeNotFound()
    {
        $this->repo->add('/file', new TestFile());

        $discovery = $this->createManageableDiscovery();

        $discovery->bind('/file', 'foo');
    }

    public function testBindIgnoresDuplicates()
    {
        $this->repo->add('/file', new TestFile());

        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type', array(
            new BindingParameter('param', false, 'default')
        )));

        // The parameter is the same both times
        $discovery->bind('/file', 'type', array('param' => 'default'));
        $discovery->bind('/file', 'type');

        $this->assertCount(1, $discovery->find('type'));
        $this->assertCount(1, $discovery->getBindings());
    }

    public function testUnbindPath()
    {
        $this->repo->add('/file1', new TestFile());
        $this->repo->add('/file2', new TestFile());

        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type1'));
        $discovery->define(new BindingType('type2'));

        $discovery->bind('/file1', 'type1');
        $discovery->bind('/file1', 'type2');
        $discovery->bind('/file2', 'type1');

        $this->assertCount(2, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(3, $discovery->getBindings());
        $this->assertCount(2, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));

        $discovery->unbind('/file1');

        $this->assertCount(1, $discovery->find('type1'));
        $this->assertCount(0, $discovery->find('type2'));
        $this->assertCount(1, $discovery->getBindings());
        $this->assertCount(0, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));
    }

    public function testUnbindPathWithType()
    {
        $this->repo->add('/file1', new TestFile());
        $this->repo->add('/file2', new TestFile());

        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type1'));
        $discovery->define(new BindingType('type2'));

        $discovery->bind('/file1', 'type1');
        $discovery->bind('/file1', 'type2');
        $discovery->bind('/file2', 'type1');

        $this->assertCount(2, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(3, $discovery->getBindings());
        $this->assertCount(2, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));

        $discovery->unbind('/file1', 'type1');

        $this->assertCount(1, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(2, $discovery->getBindings());
        $this->assertCount(1, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));
    }

    public function testUnbindPathWithTypeAndParameters()
    {
        $this->repo->add('/file1', new TestFile());
        $this->repo->add('/file2', new TestFile());

        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type1', array(
            new BindingParameter('param'),
        )));
        $discovery->define(new BindingType('type2'));

        $discovery->bind('/file1', 'type1', array(
            'param' => 'foo',
        ));
        $discovery->bind('/file1', 'type1', array(
            'param' => 'bar',
        ));
        $discovery->bind('/file1', 'type2');
        $discovery->bind('/file2', 'type1', array(
            'param' => 'foo',
        ));

        $this->assertCount(3, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(4, $discovery->getBindings());
        $this->assertCount(3, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));

        $discovery->unbind('/file1', 'type1', array(
            'param' => 'foo',
        ));

        $this->assertCount(2, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(3, $discovery->getBindings());
        $this->assertCount(2, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));
    }

    public function testUnbindPathWithParameters()
    {
        $this->repo->add('/file1', new TestFile());
        $this->repo->add('/file2', new TestFile());

        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type1', array(
            new BindingParameter('param'),
        )));
        $discovery->define(new BindingType('type2', array(
            new BindingParameter('param'),
        )));

        $discovery->bind('/file1', 'type1', array(
            'param' => 'foo',
        ));
        $discovery->bind('/file1', 'type1', array(
            'param' => 'bar',
        ));
        $discovery->bind('/file1', 'type2', array(
            'param' => 'foo',
        ));
        $discovery->bind('/file2', 'type1', array(
            'param' => 'foo',
        ));

        $this->assertCount(3, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(4, $discovery->getBindings());
        $this->assertCount(3, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));

        $discovery->unbind('/file1', null, array(
            'param' => 'foo',
        ));

        $this->assertCount(2, $discovery->find('type1'));
        $this->assertCount(0, $discovery->find('type2'));
        $this->assertCount(2, $discovery->getBindings());
        $this->assertCount(1, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));
    }

    public function testUnbindSelector()
    {
        $this->repo->add('/file1', new TestFile());
        $this->repo->add('/file2', new TestFile());

        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type1'));
        $discovery->define(new BindingType('type2'));

        $discovery->bind('/file1', 'type1');
        $discovery->bind('/file2', 'type2');
        $discovery->bind('/file*', 'type1');

        $this->assertCount(2, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(3, $discovery->getBindings());
        $this->assertCount(2, $discovery->getBindings('/file1'));
        $this->assertCount(2, $discovery->getBindings('/file2'));

        // Only the binding for "/file*" is removed, not the others
        $discovery->unbind('/file*');

        $this->assertCount(1, $discovery->find('type1'));
        $this->assertCount(1, $discovery->find('type2'));
        $this->assertCount(2, $discovery->getBindings());
        $this->assertCount(1, $discovery->getBindings('/file1'));
        $this->assertCount(1, $discovery->getBindings('/file2'));
    }

    public function testUnbindIgnoresUnknownPath()
    {
        $discovery = $this->createManageableDiscovery();

        $discovery->unbind('/foobar');

        $this->assertCount(0, $discovery->getBindings());
    }

    public function testUnbindIgnoresUnknownType()
    {
        $this->repo->add('/file1', new TestFile());

        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type1'));

        $discovery->bind('/file1', 'type1');

        $discovery->unbind('/file1', 'foobar');

        $this->assertCount(1, $discovery->find('type1'));
        $this->assertCount(1, $discovery->getBindings());
        $this->assertCount(1, $discovery->getBindings('/file1'));
    }

    public function testDefineTypeName()
    {
        $discovery = $this->createManageableDiscovery();

        $this->assertFalse($discovery->isDefined('type'));

        $discovery->define('type');

        $this->assertTrue($discovery->isDefined('type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testDefineFailsIfInvalidType()
    {
        $discovery = $this->createManageableDiscovery();

        $discovery->define(new \stdClass());
    }

    public function testDefineTypeInstance()
    {
        $discovery = $this->createManageableDiscovery();

        $this->assertFalse($discovery->isDefined('type'));

        $discovery->define(new BindingType('type'));

        $this->assertTrue($discovery->isDefined('type'));
    }

    public function testUndefine()
    {
        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type'));

        $this->assertTrue($discovery->isDefined('type'));

        $discovery->undefine('type');

        $this->assertFalse($discovery->isDefined('type'));
    }

    public function testUndefineIgnoresUnknownTypes()
    {
        $discovery = $this->createManageableDiscovery();
        $discovery->define(new BindingType('type'));

        $discovery->undefine('foobar');

        $this->assertTrue($discovery->isDefined('type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testUndefineFailsIfInvalidType()
    {
        $discovery = $this->createManageableDiscovery();

        $discovery->undefine(new \stdClass());
    }
}
