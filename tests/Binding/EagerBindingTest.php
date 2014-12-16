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

use Puli\Discovery\Binding\AbstractBinding;
use Puli\Discovery\Binding\BindingType;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Repository\Resource\Collection\ResourceCollection;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EagerBindingTest extends AbstractBindingTest
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
        $resource = new TestFile($path);

        return new EagerBinding($path, $resource, $type, $parameters);
    }

    public function testCreateFromCollection()
    {
        $resources = new ResourceCollection(array(
            $first = new TestFile('/path/file1'),
            new TestFile('/path/file2'),
        ));
        $type = new BindingType('type');

        $binding = new EagerBinding('/path/*', $resources, $type);

        $this->assertSame('/path/*', $binding->getPath());
        $this->assertSame($resources, $binding->getResources());
        $this->assertSame($first, $binding->getResource());
    }

    public function testCreateFromSingleResource()
    {
        $resource = new TestFile('/file1');
        $type = new BindingType('type');

        $binding = new EagerBinding('/file1', $resource, $type);

        $this->assertSame('/file1', $binding->getPath());
        $this->assertEquals(new ResourceCollection(array($resource)), $binding->getResources());
        $this->assertSame($resource, $binding->getResource());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testCreateFailsIfNotResourceOrCollection()
    {
        $type = new BindingType('type');

        new EagerBinding('/path/*', new \stdClass(), $type);
    }

    /**
     * @expectedException \Puli\Discovery\Binding\BindingException
     */
    public function testCreateFailsIfNoResources()
    {
        $resources = new ResourceCollection();
        $type = new BindingType('type');

        new EagerBinding('/path/*', $resources, $type);
    }
}
