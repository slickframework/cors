<?php

/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\Cors\Infrastructure\Converter;

use Slick\ErrorHandler\Exception\ExceptionInspector;
use Throwable;

/**
 * JsonMethods
 *
 * @package Slick\Cors\Infrastructure\Converter
 */
trait JsonMethods
{

    /**
     * Clears the title of a Throwable object.
     *
     * @param Throwable $throwable The Throwable object to extract the title from.
     * @return string The cleared title.
     */
    private function clearTitle(Throwable $throwable): string
    {
        $parts = explode('\\', get_class($throwable));
        $name = array_pop($parts);
        $titleParts = preg_split('/(?=[A-Z])/', $name);
        return ucfirst(trim(strtolower(implode(' ', $titleParts ?: []))));
    }

    /**
     * Generates details based on the Throwable object and ExceptionInspector.
     *
     * @param Throwable $throwable The Throwable object used to retrieve error message.
     * @param ExceptionInspector $inspector The ExceptionInspector object to provide additional information.
     * @return string The generated details based on the Throwable and ExceptionInspector.
     */
    private function details(Throwable $throwable, ExceptionInspector $inspector): string
    {
        $errorMessage = $throwable->getMessage() ? $throwable->getMessage(). " " : null;
        $help = preg_split('/\r?\n/', ltrim((string)$inspector->help()), 2);
        $help = is_array($help) ? $help[0] : '';
        $details = $errorMessage . $help[0];
        return trim($details);
    }
}
