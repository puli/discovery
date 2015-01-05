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

use Puli\Discovery\Api\BindingType;
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
     * @param string      $query
     * @param BindingType $type
     * @param array       $parameters
     * @param string      $language
     *
     * @return AbstractBinding
     */
    protected function createBinding($query, BindingType $type, array $parameters = array(), $language = 'glob')
    {
        $repo = new InMemoryRepository();
        $repo->add($query, new TestFile($query));

        return new LazyBinding($query, $repo, $type, $parameters, $language);
    }

    public function testDoNotLoadUponConstruction()
    {
        $repo = $this->getMock('Puli\Repository\Api\ResourceRepository');
        $type = new BindingType('type');

        $repo->expects($this->never())
            ->method('find');

        new LazyBinding('/path', $repo, $type);
    }

    public function testLoadOnDemand()
    {
        $repo = $this->getMock('Puli\Repository\Api\ResourceRepository');
        $type = new BindingType('type');
        $collection = new ArrayResourceCollection(array(
            new TestFile('/file1'),
            new TestFile('/file2'),
        ));

        $repo->expects($this->once())
            ->method('find')
            ->with('/file*', 'glob')
            ->will($this->returnValue($collection));

        $binding = new LazyBinding('/file*', $repo, $type);

        $this->assertSame($collection, $binding->getResources());

        // access again, no more repository calls
        $this->assertSame($collection, $binding->getResources());
    }
}
