<?php

/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\Cors\Infrastructure;

use Throwable;

/**
 * Converter
 *
 * @package Slick\Cors\Infrastructure
 */
interface Converter
{

    /**
     * Converts a Throwable object into a string representation.
     *
     * @param Throwable $throwable The Throwable object to convert
     * @return string The string representation of the Throwable object
     */
    public function convert(Throwable $throwable): string;

    /**
     * Retrieves the content type of the response.
     *
     * @return string The content type of the response
     */
    public function contentType(): string;
}
