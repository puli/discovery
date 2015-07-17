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

use Puli\Discovery\Api\Binding\BindingType;
use Puli\Discovery\Binding\AbstractBinding;
use Puli\Discovery\Binding\EagerBinding;
use Puli\Repository\Resource\Collection\ArrayResourceCollection;
use Puli\Repository\Tests\Resource\TestFile;
use stdClass;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EagerBindingTest extends AbstractBindingTest
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
        $resource = new TestFile($query);

        return new EagerBinding($query, $resource, $type, $parameters, $language);
    }

    public function testCreateFromCollection()
    {
        $resources = new ArrayResourceCollection(array(
            new TestFile('/path/file1'),
            new TestFile('/path/file2'),
        ));
        $type = new BindingType('type');

        $binding = new EagerBinding('/path/*', $resources, $type);

        $this->assertSame('/path/*', $binding->getQuery());
        $this->assertSame($resources, $binding->getResources());
    }

    public function testCreateFromSingleResource()
    {
        $resource = new TestFile('/file1');
        $type = new BindingType('type');

        $binding = new EagerBinding('/file1', $resource, $type);

        $this->assertSame('/file1', $binding->getQuery());
        $this->assertEquals(new ArrayResourceCollection(array($resource)), $binding->getResources());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testCreateFailsIfNotResourceOrCollection()
    {
        $type = new BindingType('type');

        new EagerBinding('/path/*', new stdClass(), $type);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfNoResources()
    {
        $resources = new ArrayResourceCollection();
        $type = new BindingType('type');

        new EagerBinding('/path/*', $resources, $type);
    }
}
