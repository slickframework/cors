<?php

/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\Cors;

use Slick\ModuleApi\Infrastructure\AbstractModule;
use Slick\ModuleApi\Infrastructure\FrontController\WebModuleInterface;

/**
 * CorsModule
 *
 * @package Slick\Cors
 */
final class CorsModule extends AbstractModule implements WebModuleInterface
{
    public function description(): ?string
    {
        return "Enables Cross-Origin Resource Sharing (CORS) for secure and flexible API interactions";
    }
}
