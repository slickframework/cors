<?php

/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\Cors\Infrastructure\Converter;

use Slick\Cors\Infrastructure\Converter;
use Slick\ErrorHandler\Exception\ExceptionInspector;
use Throwable;

/**
 * JsonConverter
 *
 * @package Slick\Cors\Infrastructure\Converter
 */
final class JsonConverter implements Converter
{

    use JsonMethods;

    /**
     * @inheritDoc
     */
    public function convert(Throwable $throwable): string
    {
        $inspector = new ExceptionInspector($throwable);
        $errorDetails = json_encode([
            "error" => $this->clearTitle($throwable),
            "details" => $this->details($throwable, $inspector)
        ]);
        return $errorDetails ?: '';
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/json';
    }
}
