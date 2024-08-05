<?php
/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Slick\Cors;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Slick\Cors\CorsModule;

class CorsModuleTest extends TestCase
{
    use ProphecyTrait;

    public function testDescription(): void
    {
        $module = new CorsModule();
        $this->assertIsString($module->description());
    }

    public function testSettings(): void
    {
        $env = $this->prophesize(Dotenv::class)->reveal();
        $module = new CorsModule();
        $settings = $module->settings($env)['cors'];
        $this->assertEquals("example.com", $settings['origin']);
        $this->assertEquals(
            "origin, x-requested-with, content-type, authorization",
            $settings['headers']
        );
    }
}
