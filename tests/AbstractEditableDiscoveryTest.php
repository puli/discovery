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

use Puli\Discovery\Api\Binding\BindingParameter;
use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Api\Binding\ResourceBinding;
use Puli\Discovery\Api\EditableDiscovery;
use Puli\Discovery\Api\ResourceDiscovery;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractEditableDiscoveryTest extends AbstractDiscoveryTest
{
    /**
     * @param ResourceRepository $repo
     *
     * @return EditableDiscovery
     */
    abstract protected function createEditableDiscovery(ResourceRepository $repo);

    /**
     * @param EditableDiscovery $discovery
     *
     * @return EditableDiscovery
     */
    protected function getDiscoveryUnderTest(EditableDiscovery $discovery)
    {
        return $discovery;
    }

    /**
     * @param ResourceRepository $repo
     * @param ResourceBinding[]  $bindings
     *
     * @return ResourceDiscovery
     */
    protected function createDiscovery(ResourceRepository $repo, array $bindings = array())
    {
        $discovery = $this->createEditableDiscovery($repo);

        foreach ($bindings as $binding) {
            $type = $binding->getType();

            // Prevent duplicate additions
            if (!$discovery->isTypeDefined($type->getName())) {
                $discovery->defineType($type);
            }
        }

        foreach ($bindings as $binding) {
            $discovery->bind($binding->getQuery(), $binding->getType()->getName(), $binding->getParameterValues());
        }

        return $discovery;
    }

    public function testBindSucceedsIfNoQueryMatches()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType('type');

        $discovery = $this->getDiscoveryUnderTest($discovery);

        // even if the query does not match right now, it might later when
        // resources are added to the repository
        $discovery->bind('/*.twig', 'type');

        $this->assertCount(1, $discovery->findByType('type'));
        $this->assertCount(1, $discovery->getBindings());
    }

    /**
     * @expectedException \Puli\Discovery\Api\NoSuchTypeException
     * @expectedExceptionMessage foo
     */
    public function testBindFailsIfTypeNotFound()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file'),
        ));

        $discovery = $this->createEditableDiscovery($repo);

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->bind('/file', 'foo');
    }

    /**
     * @expectedException \Puli\Repository\Api\UnsupportedLanguageException
     * @expectedExceptionMessage foo
     */
    public function testBindFailsIfUnsupportedLanguage()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file'),
        ));

        $discovery = $this->createEditableDiscovery($repo);

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->defineType(new BindingType('type'));
        $discovery->bind('/file', 'type', array(), 'foo');
    }

    public function testBindIgnoresDuplicates()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type', array(
            new BindingParameter('param', BindingParameter::OPTIONAL, 'default')
        )));
        $discovery->bind('/file', 'type', array('param' => 'default'));

        // The parameter is the same both times
        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->bind('/file', 'type');

        $this->assertCount(1, $discovery->findByType('type'));
        $this->assertCount(1, $discovery->getBindings());
    }

    public function testUnbindPath()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type1'));
        $discovery->defineType(new BindingType('type2'));

        $discovery->bind('/file1', 'type1');
        $discovery->bind('/file1', 'type2');
        $discovery->bind('/file2', 'type1');

        $this->assertCount(2, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(2, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(3, $discovery->getBindings());

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/file1');

        $this->assertCount(1, $discovery->findByType('type1'));
        $this->assertCount(0, $discovery->findByType('type2'));
        $this->assertCount(0, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(1, $discovery->getBindings());
    }

    public function testUnbindPathWithType()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type1'));
        $discovery->defineType(new BindingType('type2'));

        $discovery->bind('/file1', 'type1');
        $discovery->bind('/file1', 'type2');
        $discovery->bind('/file2', 'type1');

        $this->assertCount(2, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(2, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(3, $discovery->getBindings());

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/file1', 'type1');

        $this->assertCount(1, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(1, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(2, $discovery->getBindings());
    }

    public function testUnbindPathWithTypeAndParameters()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type1', array(
            new BindingParameter('param'),
        )));
        $discovery->defineType(new BindingType('type2'));

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

        $this->assertCount(3, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(3, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(4, $discovery->getBindings());

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/file1', 'type1', array(
            'param' => 'foo',
        ));

        $this->assertCount(2, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(2, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(3, $discovery->getBindings());
    }

    public function testUnbindPathWithParameters()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type1', array(
            new BindingParameter('param'),
        )));
        $discovery->defineType(new BindingType('type2', array(
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

        $this->assertCount(3, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(3, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(4, $discovery->getBindings());

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/file1', null, array(
            'param' => 'foo',
        ));

        $this->assertCount(2, $discovery->findByType('type1'));
        $this->assertCount(0, $discovery->findByType('type2'));
        $this->assertCount(1, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(2, $discovery->getBindings());
    }

    public function testUnbindQuery()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type1'));
        $discovery->defineType(new BindingType('type2'));

        $discovery->bind('/file1', 'type1');
        $discovery->bind('/file2', 'type2');
        $discovery->bind('/file*', 'type1');

        $this->assertCount(2, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(2, $discovery->findByPath('/file1'));
        $this->assertCount(2, $discovery->findByPath('/file2'));
        $this->assertCount(3, $discovery->getBindings());

        // Only the binding for "/file*" is removed, not the others
        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/file*');

        $this->assertCount(1, $discovery->findByType('type1'));
        $this->assertCount(1, $discovery->findByType('type2'));
        $this->assertCount(1, $discovery->findByPath('/file1'));
        $this->assertCount(1, $discovery->findByPath('/file2'));
        $this->assertCount(2, $discovery->getBindings());
    }

    /**
     * @expectedException \Puli\Repository\Api\UnsupportedLanguageException
     * @expectedExceptionMessage foo
     */
    public function testUnbindFailsIfUnsupportedLanguage()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type'));
        $discovery->bind('/file*', 'type');

        // Only the binding for "/file*" is removed, not the others
        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/file*', null, null, 'foo');
    }

    public function testUnbindIgnoresUnknownPath()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/foobar');

        $this->assertCount(0, $discovery->getBindings());
    }

    public function testUnbindIgnoresUnknownType()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type'));

        $discovery->bind('/file', 'type');

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->unbind('/file', 'foobar');

        $this->assertCount(1, $discovery->findByType('type'));
        $this->assertCount(1, $discovery->findByPath('/file'));
        $this->assertCount(1, $discovery->getBindings());
    }

    public function testDefineTypeName()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);

        $this->assertFalse($discovery->isTypeDefined('type'));

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->defineType('type');

        $this->assertTrue($discovery->isTypeDefined('type'));
    }

    public function testDefineTypeInstance()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);

        $this->assertFalse($discovery->isTypeDefined('type'));

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->defineType(new BindingType('type'));

        $this->assertTrue($discovery->isTypeDefined('type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testDefineFailsIfInvalidType()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->defineType(new \stdClass());
    }

    /**
     * @expectedException \Puli\Discovery\Api\DuplicateTypeException
     * @expectedExceptionMessage type
     */
    public function testDefineFailsIfAlreadyDefined()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType('type');

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->defineType('type');
    }

    public function testUndefine()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type'));

        $this->assertTrue($discovery->isTypeDefined('type'));

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->undefineType('type');

        $this->assertFalse($discovery->isTypeDefined('type'));
    }

    public function testUndefineIgnoresUnknownTypes()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type'));

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->undefineType('foobar');

        $this->assertTrue($discovery->isTypeDefined('type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testUndefineFailsIfInvalidType()
    {
        $repo = $this->createRepository();
        $discovery = $this->createEditableDiscovery($repo);

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->undefineType(new \stdClass());
    }

    public function testUndefineRemovesCorrespondingBindings()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type'));
        $discovery->bind('/file', 'type');

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->undefineType('type');

        $this->assertFalse($discovery->isTypeDefined('type'));
        $this->assertCount(0, $discovery->findByPath('/file'));
    }

    public function testClear()
    {
        $repo = $this->createRepository(array(
            new TestFile('/file'),
        ));

        $discovery = $this->createEditableDiscovery($repo);
        $discovery->defineType(new BindingType('type'));
        $discovery->bind('/file', 'type');

        $discovery = $this->getDiscoveryUnderTest($discovery);
        $discovery->clear();

        $this->assertSame(array(), $discovery->getBindings());
        $this->assertSame(array(), $discovery->getDefinedTypes());

    }
}
