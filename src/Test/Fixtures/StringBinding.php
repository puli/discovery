<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Test\Fixtures;

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Binding\AbstractBinding;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @internal
 */
class StringBinding extends AbstractBinding
{
    private $string;

    public function __construct($string, $typeName, array $parameterValues = array())
    {
        parent::__construct($typeName, $parameterValues);

        $this->string = $string;
    }

    public function getString()
    {
        return $this->string;
    }

    public function equals(Binding $other)
    {
        if (!parent::equals($other)) {
            return false;
        }

        /* @var StringBinding $other */
        return $this->string === $other->string;
    }

    protected function preSerialize(array &$data)
    {
        parent::preSerialize($data);

        $data[] = $this->string;
    }

    protected function postUnserialize(array &$data)
    {
        $this->string = array_pop($data);

        parent::postUnserialize($data);
    }
}
