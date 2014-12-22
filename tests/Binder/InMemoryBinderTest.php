<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests\Binder;

use Puli\Discovery\Binder\InMemoryBinder;
use Puli\Discovery\Binding\BindingParameter;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\Tests\AbstractResourceDiscoveryTest;
use Puli\Repository\InMemoryRepository;
use Puli\Repository\ManageableRepository;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InMemoryBinderTest extends AbstractResourceDiscoveryTest
{
    /**
     * Creates a binder for a set of bindings.
     *
     * @param ManageableRepository       $repo     The repository to use.
     * @param ResourceBinding[] $bindings The bindings to store.
     *
     * @return InMemoryBinder The created binder.
     */
    public static function createBinder(ManageableRepository $repo, array $bindings = array())
    {
        foreach ($bindings as $binding) {
            foreach ($binding->getResources() as $resource) {
                $path = $resource->getPath();

                // Prevent duplicate additions
                if (!$repo->contains($path) || $resource !== $repo->get($path)) {
                    $repo->add($path, $resource);
                }
            }
        }

        $binder = new InMemoryBinder($repo);

        foreach ($bindings as $binding) {
            $type = $binding->getType();

            // Prevent duplicate additions
            if (!$binder->isDefined($type->getName())) {
                $binder->define($type);
            }
        }

        foreach ($bindings as $binding) {
            $binder->bind($binding->getPath(), $binding->getType()->getName(), $binding->getParameters());
        }

        return $binder;
    }

    /**
     * {@inheritdoc}
     */
    protected function createDiscovery(array $bindings = array())
    {
        return self::createBinder(new InMemoryRepository(), $bindings);
    }

    /**
     * @expectedException \Puli\Discovery\Binding\BindingException
     * @expectedExceptionMessage /foo
     */
    public function testBindFailsIfResourceNotFound()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);
        $binder->define('type');

        $binder->bind('/foo', 'type');
    }

    /**
     * @expectedException \Puli\Discovery\Binding\NoSuchTypeException
     * @expectedExceptionMessage foo
     */
    public function testBindFailsIfTypeNotFound()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file', new TestFile());

        $binder = new InMemoryBinder($repo);

        $binder->bind('/file', 'foo');
    }

    public function testBindIgnoresDuplicates()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file', new TestFile());

        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type', array(
            new BindingParameter('param', null, 'default')
        )));

        // The parameter is the same both times
        $binder->bind('/file', 'type', array('param' => 'default'));
        $binder->bind('/file', 'type');

        $this->assertCount(1, $binder->find('type'));
        $this->assertCount(1, $binder->getBindings());
    }

    public function testUnbindPath()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file1', new TestFile());
        $repo->add('/file2', new TestFile());

        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type1'));
        $binder->define(new BindingType('type2'));

        $binder->bind('/file1', 'type1');
        $binder->bind('/file1', 'type2');
        $binder->bind('/file2', 'type1');

        $this->assertCount(2, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(3, $binder->getBindings());
        $this->assertCount(2, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));

        $binder->unbind('/file1');

        $this->assertCount(1, $binder->find('type1'));
        $this->assertCount(0, $binder->find('type2'));
        $this->assertCount(1, $binder->getBindings());
        $this->assertCount(0, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));
    }

    public function testUnbindPathWithType()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file1', new TestFile());
        $repo->add('/file2', new TestFile());

        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type1'));
        $binder->define(new BindingType('type2'));

        $binder->bind('/file1', 'type1');
        $binder->bind('/file1', 'type2');
        $binder->bind('/file2', 'type1');

        $this->assertCount(2, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(3, $binder->getBindings());
        $this->assertCount(2, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));

        $binder->unbind('/file1', 'type1');

        $this->assertCount(1, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(2, $binder->getBindings());
        $this->assertCount(1, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));
    }

    public function testUnbindPathWithTypeAndParameters()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file1', new TestFile());
        $repo->add('/file2', new TestFile());

        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type1', array(
            new BindingParameter('param'),
        )));
        $binder->define(new BindingType('type2'));

        $binder->bind('/file1', 'type1', array(
            'param' => 'foo',
        ));
        $binder->bind('/file1', 'type1', array(
            'param' => 'bar',
        ));
        $binder->bind('/file1', 'type2');
        $binder->bind('/file2', 'type1', array(
            'param' => 'foo',
        ));

        $this->assertCount(3, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(4, $binder->getBindings());
        $this->assertCount(3, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));

        $binder->unbind('/file1', 'type1', array(
            'param' => 'foo',
        ));

        $this->assertCount(2, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(3, $binder->getBindings());
        $this->assertCount(2, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));
    }

    public function testUnbindPathWithParameters()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file1', new TestFile());
        $repo->add('/file2', new TestFile());

        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type1', array(
            new BindingParameter('param'),
        )));
        $binder->define(new BindingType('type2', array(
            new BindingParameter('param'),
        )));

        $binder->bind('/file1', 'type1', array(
            'param' => 'foo',
        ));
        $binder->bind('/file1', 'type1', array(
            'param' => 'bar',
        ));
        $binder->bind('/file1', 'type2', array(
            'param' => 'foo',
        ));
        $binder->bind('/file2', 'type1', array(
            'param' => 'foo',
        ));

        $this->assertCount(3, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(4, $binder->getBindings());
        $this->assertCount(3, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));

        $binder->unbind('/file1', null, array(
            'param' => 'foo',
        ));

        $this->assertCount(2, $binder->find('type1'));
        $this->assertCount(0, $binder->find('type2'));
        $this->assertCount(2, $binder->getBindings());
        $this->assertCount(1, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));
    }

    public function testUnbindSelector()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file1', new TestFile());
        $repo->add('/file2', new TestFile());

        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type1'));
        $binder->define(new BindingType('type2'));

        $binder->bind('/file1', 'type1');
        $binder->bind('/file2', 'type2');
        $binder->bind('/file*', 'type1');

        $this->assertCount(2, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(3, $binder->getBindings());
        $this->assertCount(2, $binder->getBindings('/file1'));
        $this->assertCount(2, $binder->getBindings('/file2'));

        // Only the binding for "/file*" is removed, not the others
        $binder->unbind('/file*');

        $this->assertCount(1, $binder->find('type1'));
        $this->assertCount(1, $binder->find('type2'));
        $this->assertCount(2, $binder->getBindings());
        $this->assertCount(1, $binder->getBindings('/file1'));
        $this->assertCount(1, $binder->getBindings('/file2'));
    }

    public function testUnbindIgnoresUnknownPath()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);

        $binder->unbind('/foobar');

        $this->assertCount(0, $binder->getBindings());
    }

    public function testUnbindIgnoresUnknownType()
    {
        $repo = new InMemoryRepository();
        $repo->add('/file1', new TestFile());

        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type1'));

        $binder->bind('/file1', 'type1');

        $binder->unbind('/file1', 'foobar');

        $this->assertCount(1, $binder->find('type1'));
        $this->assertCount(1, $binder->getBindings());
        $this->assertCount(1, $binder->getBindings('/file1'));
    }

    public function testDefineTypeName()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);

        $this->assertFalse($binder->isDefined('type'));

        $binder->define('type');

        $this->assertTrue($binder->isDefined('type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testDefineFailsIfInvalidType()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);

        $binder->define(new \stdClass());
    }

    public function testDefineTypeInstance()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);

        $this->assertFalse($binder->isDefined('type'));

        $binder->define(new BindingType('type'));

        $this->assertTrue($binder->isDefined('type'));
    }

    public function testUndefine()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type'));

        $this->assertTrue($binder->isDefined('type'));

        $binder->undefine('type');

        $this->assertFalse($binder->isDefined('type'));
    }

    public function testUndefineIgnoresUnknownTypes()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);
        $binder->define(new BindingType('type'));

        $binder->undefine('foobar');

        $this->assertTrue($binder->isDefined('type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testUndefineFailsIfInvalidType()
    {
        $repo = new InMemoryRepository();
        $binder = new InMemoryBinder($repo);

        $binder->undefine(new \stdClass());
    }
}
