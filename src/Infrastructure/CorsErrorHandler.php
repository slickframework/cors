<?php

/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\Cors\Infrastructure;

use Slick\Configuration\ConfigurationInterface;
use Slick\Cors\CorsModule;
use Slick\ErrorHandler\Exception\ExceptionInspector;
use Slick\ErrorHandler\Handler\HandlerInterface;
use Slick\ErrorHandler\RunnerInterface;
use Throwable;
use function Slick\ModuleApi\importSettingsFile;

/**
 * CorsErrorHandler
 *
 * @package Slick\Cors\Infrastructure
 */
final readonly class CorsErrorHandler implements HandlerInterface
{

    public function __construct(private ConfigurationInterface $config)
    {
    }

    public function handle(Throwable $throwable, ExceptionInspector $inspector, RunnerInterface $runner): ?int
    {
        $runner->outputHeaders([
            "Access-Control-Allow-Origin" => $this->config->get('cors.origin'),
            "Access-Control-Allow-Methods" => $this->config->get('cors.methods'),
            "Access-Control-Allow-Headers" => $this->config->get('cors.headers'),
            "Access-Control-Allow-Credentials" => $this->config->get('cors.credentials'),
        ]);
        return $this::DONE;
    }

    /**
     * Create a CorsErrorHandler instance.
     *
     * @return CorsErrorHandler Returns a new CorsErrorHandler object.
     */
    public static function create(string $appRoot): CorsErrorHandler
    {
        $file = $appRoot . '/config/modules/cors.php';
        $settings = importSettingsFile($file, CorsModule::$defaultConfig);
        $driver = new ArrayConfigurationDriver($settings);
        return new self($driver);
    }
}
