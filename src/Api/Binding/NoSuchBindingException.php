<?php

/*
 * This file is part of the webmozart/booking package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Discovery\Api\Binding;

use Exception;
use Rhumsaa\Uuid\Uuid;
use RuntimeException;

/**
 * Thrown when a binding was not found.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NoSuchBindingException extends RuntimeException
{
    /**
     * Creates an exception for a UUID.
     *
     * @param Uuid           $uuid  The UUID of the binding.
     * @param Exception|null $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forUuid(Uuid $uuid, Exception $cause = null)
    {
        return new static(sprintf(
            'The binding "%s" does not exist.',
            $uuid->toString()
        ), 0, $cause);
    }
}
