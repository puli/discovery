<?php

/*
 * This file is part of the webmozart/booking package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Tests\Binding;

use Puli\Discovery\Binding\ClassBinding;
use Puli\Discovery\Test\AbstractBindingTest;
use Puli\Discovery\Test\Fixtures\Foo;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ClassBindingTest extends AbstractBindingTest
{
    protected function createBinding($typeName, array $parameterValues = array())
    {
        return new ClassBinding(__CLASS__, $typeName, $parameterValues);
    }

    public function testCreateWithClassName()
    {
        $binding = new ClassBinding(__CLASS__, Foo::clazz);

        $this->assertSame(__CLASS__, $binding->getClassName());
    }
}
