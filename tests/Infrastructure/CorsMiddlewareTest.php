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
use Slick\Cors\Infrastructure\Converter;
use Slick\Cors\Infrastructure\CorsMiddleware;
use PHPUnit\Framework\TestCase;
use Slick\Http\Message\Response;
use Slick\Http\Message\Uri;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class CorsMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessOptions(): void
    {
        $settings = $this->mockConfigurationSettings();
        $urlMatcher = $this->prophesize(UrlMatcherInterface::class);
        $urlMatcher->match("/test")->willReturn([]);
        $middleware = new CorsMiddleware(
            $settings->reveal(),
            $urlMatcher->reveal(),
            $this->createConvertedMock()->reveal()
        );

        $this->assertInstanceOf(CorsMiddleware::class, $middleware);
        $request = $this->createRequest("OPTIONS");
        $handler = $this->prophesize(RequestHandlerInterface::class)->reveal();
        $response = $middleware->process($request->reveal(), $handler);
        $this->verifyCorsHeadersAreSet($response);
    }

    public function testProcessNotFound(): void
    {
        $settings = $this->mockConfigurationSettings();
        $urlMatcher = $this->prophesize(UrlMatcherInterface::class);
        $urlMatcher->match("/test")->willthrow(new ResourceNotFoundException());

        $middleware = new CorsMiddleware(
            $settings->reveal(),
            $urlMatcher->reveal(),
            $this->createConvertedMock()->reveal()
        );
        $request = $this->createRequest();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $response = $middleware->process($request->reveal(), $handler->reveal());
        $this->verifyCorsHeadersAreSet($response);
    }
    public function testProcessMethodNotAllowedException(): void
    {
        $settings = $this->mockConfigurationSettings();
        $urlMatcher = $this->prophesize(UrlMatcherInterface::class);
        $urlMatcher->match("/test")->willthrow(new MethodNotAllowedException(['POST']));

        $middleware = new CorsMiddleware(
            $settings->reveal(),
            $urlMatcher->reveal(),
            $this->createConvertedMock()->reveal()
        );
        $request = $this->createRequest();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $response = $middleware->process($request->reveal(), $handler->reveal());
        $this->verifyCorsHeadersAreSet($response);
    }

    public function testProcess(): void
    {
        $urlMatcher = $this->prophesize(UrlMatcherInterface::class);
        $urlMatcher->match("/test")->willReturn([]);
        $settings = $this->mockConfigurationSettings();
        $middleware = new CorsMiddleware(
            $settings->reveal(),
            $urlMatcher->reveal(),
            $this->createConvertedMock()->reveal()
        );
        $this->assertInstanceOf(CorsMiddleware::class, $middleware);
        $request = $this->createRequest();
        $response = new Response(200);
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);
        $response = $middleware->process($request->reveal(), $handler->reveal());
        $this->verifyCorsHeadersAreSet($response);
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

    /**
     * @return ObjectProphecy|ServerRequestInterface
     */
    public function createRequest(string $method = 'GET'): ServerRequestInterface|ObjectProphecy
    {
        $uri = new Uri('https://example.com/test');
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn($method);
        $request->getUri()->willReturn($uri);
        return $request;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    public function verifyCorsHeadersAreSet(\Psr\Http\Message\ResponseInterface $response): void
    {
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

    private function createConvertedMock(): ObjectProphecy
    {
        $converted = $this->prophesize(Converter::class);
        $converted->convert(Argument::type(\Throwable::class))->willReturn('test');
        $converted->contentType()->willReturn('application/json');
        return $converted;
    }
}
