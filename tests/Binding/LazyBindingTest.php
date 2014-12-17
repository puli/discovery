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

use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\AbstractBinding;
use Puli\Discovery\Binding\LazyBinding;
use Puli\Repository\InMemoryRepository;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LazyBindingTest extends AbstractBindingTest
{
    /**
     * @param string      $path
     * @param BindingType $type
     * @param array       $parameters
     *
     * @return AbstractBinding
     */
    protected function createBinding($path, BindingType $type, array $parameters = array())
    {
        $repo = new InMemoryRepository();
        $repo->add($path, new TestFile($path));

        return new LazyBinding($path, $repo, $type, $parameters);
    }

    public function testDoNotLoadUponConstruction()
    {
        $repo = $this->getMock('Puli\Repository\ResourceRepository');
        $type = new BindingType('type');

        $repo->expects($this->never())
            ->method('find');

        new LazyBinding('/path', $repo, $type);
    }

    public function testLoadOnDemand()
    {
        $repo = $this->getMock('Puli\Repository\ResourceRepository');
        $type = new BindingType('type');
        $collection = new ArrayResourceCollection(array(
            $first = new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $repo->expects($this->once())
            ->method('find')
            ->with('/file*')
            ->will($this->returnValue($collection));

        $binding = new LazyBinding('/file*', $repo, $type);

        $this->assertSame($collection, $binding->getResources());
        $this->assertSame($first, $binding->getResource());

        // access again, no more repository calls
        $this->assertSame($collection, $binding->getResources());
    }
}
