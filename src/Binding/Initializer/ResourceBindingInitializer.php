<?php

/*
 * This file is part of the puli/discovery package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Binding\Initializer;

use Puli\Discovery\Api\Binding\Binding;
use Puli\Discovery\Api\Binding\Initializer\BindingInitializer;
use Puli\Discovery\Binding\ResourceBinding;
use Puli\Repository\Api\ResourceRepository;
use Webmozart\Assert\Assert;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResourceBindingInitializer implements BindingInitializer
{
    /**
     * The accepted binding class name.
     */
    const CLASS_NAME = 'Puli\Discovery\Binding\ResourceBinding';

    /**
     * @var ResourceRepository
     */
    private $repo;

    /**
     * @param ResourceRepository $repo
     */
    public function __construct(ResourceRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptsBinding($className)
    {
        return self::CLASS_NAME === $className || is_subclass_of($className, self::CLASS_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function initializeBinding(Binding $binding)
    {
        Assert::isInstanceOf($binding, self::CLASS_NAME, 'The binding must be an instance of ResourceBinding. Got: %s');

        /* @var ResourceBinding $binding */
        $binding->setRepository($this->repo);
    }
}
