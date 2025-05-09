<?php

/**
 * This file is part of Cors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Slick\Cors\Infrastructure;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slick\Configuration\ConfigurationInterface;
use Slick\Http\Message\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * CorsMiddleware
 *
 * @package Slick\Cors\Infrastructure
 */
final readonly class CorsMiddleware implements MiddlewareInterface
{

    public function __construct(
        private ConfigurationInterface $config,
        private UrlMatcherInterface $matcher,
        private Converter $converter,
        private string $routingBasePath = ''
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isOptions = strtoupper($request->getMethod()) === 'OPTIONS';
        $headers = ['Content-Type' => $this->converter->contentType()];
        $pathinfo = $request->getUri()->getPath();
        $basePath = $this->routingBasePath;
        $path = str_replace('//', '/', '/'.str_replace($basePath, '', $pathinfo));

        try {
            $isOptions ?: $this->matcher->match($path);
        } catch (MethodNotAllowedException $exception) {
            return $this->addHeaders(new Response(405, $this->toJson($exception), $headers), $request);
        } catch (NoConfigurationException|ResourceNotFoundException $exception) {
            return $this->addHeaders(new Response(404, $this->toJson($exception), $headers), $request);
        }

        $response = $isOptions
            ? (new Response(200))->withHeader('Content-Type', $headers['Content-Type'])
            : $handler->handle($request);
        return $this->addHeaders($response, $request);
    }

    private function addHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $referer = $request->hasHeader('origin')
            ? $request->getHeaderLine('origin')
            : '*';
        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->config->get('cors.origin', $referer))
            ->withHeader('Access-Control-Allow-Methods', $this->config->get('cors.methods'))
            ->withHeader('Access-Control-Allow-Headers', $this->config->get('cors.headers'))
            ->withHeader('Access-Control-Allow-Credentials', $this->config->get('cors.credentials'))
            ;
    }

    /**
     * @param Exception|MethodNotAllowedException $exception
     * @return string
     */
    public function toJson(Exception|MethodNotAllowedException $exception): string
    {
        return $this->converter->convert($exception);
    }
}
