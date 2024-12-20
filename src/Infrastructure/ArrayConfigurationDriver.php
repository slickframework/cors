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
use Slick\Configuration\Driver\CommonDriverMethods;

/**
 * ArrayConfigurationDriver
 *
 * @package Slick\Cors\Infrastructure
 */
final class ArrayConfigurationDriver implements ConfigurationInterface
{

    use CommonDriverMethods;

    /**
     * Creates a ArrayConfigurationDriver
     *
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
