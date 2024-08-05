<?php
/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Slick\Cors\Infrastructure;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slick\Configuration\ConfigurationInterface;
use Slick\Cors\Infrastructure\CorsMiddleware;
use PHPUnit\Framework\TestCase;
use Slick\Http\Message\Response;

class CorsMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessOptions(): void
    {
        $settings = $this->mockConfigurationSettings();
        $middleware = new CorsMiddleware($settings->reveal());

        $this->assertInstanceOf(CorsMiddleware::class, $middleware);
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn('OPTIONS');
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();
        $response = $middleware->process($request->reveal(), $handler);
        $this->assertEquals('www.example.com', $response->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals(
            'origin, x-requested-with, content-type, authorization',
            $response->getHeaderLine('Access-Control-Allow-Headers')
        );
        $this->assertEquals(
            'GET, POST, PATCH, PUT, HEAD, DELETE, OPTIONS',
            $response->getHeaderLine('Access-Control-Allow-Methods')
        );
        $this->assertEquals(
            'true',
            $response->getHeaderLine('Access-Control-Allow-Credentials')
        );
    }

    public function testProcess(): void
    {
        $settings = $this->mockConfigurationSettings();
        $middleware = new CorsMiddleware($settings->reveal());

        $this->assertInstanceOf(CorsMiddleware::class, $middleware);
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn('GET');
        $response = new Response(200);
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);
        $response = $middleware->process($request->reveal(), $handler->reveal());
        $this->assertEquals('www.example.com', $response->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals(
            'origin, x-requested-with, content-type, authorization',
            $response->getHeaderLine('Access-Control-Allow-Headers')
        );
        $this->assertEquals(
            'GET, POST, PATCH, PUT, HEAD, DELETE, OPTIONS',
            $response->getHeaderLine('Access-Control-Allow-Methods')
        );
        $this->assertEquals(
            'true',
            $response->getHeaderLine('Access-Control-Allow-Credentials')
        );
    }

    /**
     * @return ObjectProphecy|ConfigurationInterface
     */
    private function mockConfigurationSettings(): ObjectProphecy|ConfigurationInterface
    {
        $settings = $this->prophesize(ConfigurationInterface::class);
        $settings->get('cors.origin')->shouldBeCalled()->willReturn("www.example.com");
        $settings->get('cors.headers')
            ->shouldBeCalled()
            ->willReturn("origin, x-requested-with, content-type, authorization");
        $settings->get('cors.methods')
            ->shouldBeCalled()
            ->willReturn("GET, POST, PATCH, PUT, HEAD, DELETE, OPTIONS");
        $settings->get('cors.credentials')->shouldBeCalled()->willReturn("true");
        return $settings;
    }
}
