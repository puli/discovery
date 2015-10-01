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

use Puli\Discovery\Binding\ResourceBinding;
use Puli\Discovery\Tests\Fixtures\Foo;
use Ramsey\Uuid\Uuid;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResourceBindingTest extends AbstractBindingTest
{
    protected function createBinding($typeName, array $parameterValues = array(), Uuid $uuid = null)
    {
        return new ResourceBinding('/path/*', $typeName, $parameterValues, 'glob', $uuid);
    }

    public function testCreateWithQuery()
    {
        $binding = new ResourceBinding('/path/*', Foo::clazz, array(), 'glob');

        $this->assertSame('/path/*', $binding->getQuery());
        $this->assertSame('glob', $binding->getLanguage());
        $this->assertSame(Foo::clazz, $binding->getTypeName());
    }

    public function testGetResources()
    {
        $repo = $this->getMock('Puli\Repository\Api\ResourceRepository');
        $binding = new ResourceBinding('/path/*', Foo::clazz, array(), 'language');

        $repo->expects($this->once())
            ->method('find')
            ->with('/path/*', 'language')
            ->willReturn('RESULT');

        $binding->setRepository($repo);

        $this->assertSame('RESULT', $binding->getResources());
    }

    /**
     * @expectedException \Puli\Discovery\Api\Binding\Initializer\NotInitializedException
     */
    public function testGetResourcesFailsIfNotSet()
    {
        $binding = new ResourceBinding('/path/*', Foo::clazz, array(), 'language');

        $binding->getResources();
    }
}
