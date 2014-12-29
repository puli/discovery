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
use Puli\Discovery\Binding\EagerBinding;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
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
        $resources = new ArrayResourceCollection(array(
            new TestFile('/path/file1'),
            new TestFile('/path/file2'),
        ));
        $type = new BindingType('type');

        $binding = new EagerBinding('/path/*', $resources, $type);

        $this->assertSame('/path/*', $binding->getPath());
        $this->assertSame($resources, $binding->getResources());
    }

    public function testCreateFromSingleResource()
    {
        $resource = new TestFile('/file1');
        $type = new BindingType('type');

        $binding = new EagerBinding('/file1', $resource, $type);

        $this->assertSame('/file1', $binding->getPath());
        $this->assertEquals(new ArrayResourceCollection(array($resource)), $binding->getResources());
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
     * @expectedException \Puli\Discovery\Api\BindingException
     */
    public function testCreateFailsIfNoResources()
    {
        $resources = new ArrayResourceCollection();
        $type = new BindingType('type');

        new EagerBinding('/path/*', $resources, $type);
    }
}
