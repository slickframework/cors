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
        private UrlMatcherInterface $matcher
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->matcher->match($request->getUri()->getPath());
        } catch (MethodNotAllowedException $exception) {
            return $this->addHeaders(new Response(405, $this->toJson($exception)));
        } catch (NoConfigurationException|ResourceNotFoundException $exception) {
            return $this->addHeaders(new Response(404, $this->toJson($exception)));
        }

        $response = strtoupper($request->getMethod()) === 'OPTIONS' ? new Response(200) : $handler->handle($request);
        return $this->addHeaders($response);
    }

    private function addHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->config->get('cors.origin'))
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
        $message = json_encode($exception->getMessage());
        return $message ?: "";
    }
}
