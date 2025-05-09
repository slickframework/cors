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
use Slick\JSONAPI\JsonApi;
use Slick\JSONAPI\Object\ErrorObject;
use Throwable;

/**
 * JsonApiConverter
 *
 * @package Slick\Cors\Infrastructure\Converter
 */
final class JsonApiConverter implements Converter
{

    use JsonMethods;

    /**
     * @inheritDoc
     */
    public function convert(Throwable $throwable): string
    {
        $inspector = new ExceptionInspector($throwable);
        $jsonApiError = new ErrorObject(
            title: $this->clearTitle($throwable),
            detail: $this->details($throwable, $inspector),
            status: (string) $inspector->statusCode()
        );
        $response = [
            "jsonapi" => ["version" => JsonApi::JSON_API_11],
            "errors" => [
                $jsonApiError->withIdentifier(uniqid())
            ]
        ];
        $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return $jsonResponse ?: '';
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/vnd.api+json';
    }
}
