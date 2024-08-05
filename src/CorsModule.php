<?php

/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\Cors;

use Dotenv\Dotenv;
use Slick\Cors\Infrastructure\CorsMiddleware;
use Slick\ModuleApi\Infrastructure\AbstractModule;
use Slick\ModuleApi\Infrastructure\FrontController\MiddlewareHandler;
use Slick\ModuleApi\Infrastructure\FrontController\MiddlewareHandlerInterface;
use Slick\ModuleApi\Infrastructure\FrontController\MiddlewarePosition;
use Slick\ModuleApi\Infrastructure\FrontController\Position;
use Slick\ModuleApi\Infrastructure\FrontController\WebModuleInterface;
use function Slick\ModuleApi\importSettingsFile;

/**
 * CorsModule
 *
 * @package Slick\Cors
 */
final class CorsModule extends AbstractModule implements WebModuleInterface
{
    /** @var array<array<string, mixed>>  */
    private static array $defaultConfig = [
        'cors' => [
            'origin' => '*',
            'methods' => 'GET, POST, PATCH, PUT, HEAD, DELETE, OPTIONS',
            'headers' => 'origin, x-requested-with, content-type, authorization',
            'credentials' => 'true'
        ]
    ];

    public function description(): string
    {
        return "Enables Cross-Origin Resource Sharing (CORS) for secure and flexible API interactions.";
    }

    /**
     * Retrieves the settings from a configuration file.
     *
     * @param Dotenv $dotenv The Dotenv instance used for loading environment variables.
     * @return array<string|mixed> The array containing the settings.
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function settings(Dotenv $dotenv): array
    {
        $file = APP_ROOT . '/config/modules/cors.php';
        return importSettingsFile($file, self::$defaultConfig);
    }

    /**
     * Retrieves an array of middleware handlers.
     *
     * @return array<MiddlewareHandlerInterface> The array of middleware handlers.
     */
    public function middlewareHandlers(): array
    {
        $position = new MiddlewarePosition(Position::Top);

        return [
            new MiddlewareHandler(
                'cors',
                $position,
                CorsMiddleware::class
            )
        ];
    }
}
