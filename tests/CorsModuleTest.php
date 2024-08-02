<?php
/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Slick\Cors;

use Slick\Cors\CorsModule;
use PHPUnit\Framework\TestCase;

class CorsModuleTest extends TestCase
{

    public function testDescription(): void
    {
        $module = new CorsModule();
        $this->assertIsString($module->description());
    }
}
