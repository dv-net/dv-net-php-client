<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\SimpleHttp;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
    private string $method;
    private UriInterface $uri;
    /** @var array<string, string[]> */
    private array $headers = [];
    /** @var array<string, string> */
    private array $headerNames = [];
    private StreamInterface $body;
    private string $protocolVersion;
    private ?string $requestTarget = null;

    /**
     * @param array<string, string|string[]> $headers
     */
    public function __construct(
        string $method,
        UriInterface $uri,
        array $headers = [],
        ?StreamInterface $body = null,
        string $protocolVersion = '1.1'
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->body = $body ?? new Stream(fopen('php://temp', 'r+'));
        $this->protocolVersion = $protocolVersion;

        foreach ($headers as $name => $value) {
            $value = (array) $value;
            $normalized = strtolower($name);
            if (isset($this->headerNames[$normalized])) {
                $originalName = $this->headerNames[$normalized];
                unset($this->headers[$originalName]);
            }
            $this->headerNames[$normalized] = $name;
            $this->headers[$name] = $value;
        }
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        if ($version === $this->protocolVersion) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    public function getHeader(string $name): array
    {
        $normalized = strtolower($name);
        if (!isset($this->headerNames[$normalized])) {
            return [];
        }

        $originalName = $this->headerNames[$normalized];

        return $this->headers[$originalName];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $value = (array) $value;
        $normalized = strtolower($name);

        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $originalName = $new->headerNames[$normalized];
            unset($new->headers[$originalName]);
        }

        $new->headerNames[$normalized] = $name;
        $new->headers[$name] = $value;

        return $new;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $value = (array) $value;
        $normalized = strtolower($name);

        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $originalName = $new->headerNames[$normalized];
            $new->headers[$originalName] = array_merge($new->headers[$originalName], $value);
        } else {
            $new->headerNames[$normalized] = $name;
            $new->headers[$name] = $value;
        }

        return $new;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $normalized = strtolower($name);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $new = clone $this;
        $originalName = $new->headerNames[$normalized];
        unset($new->headers[$originalName]);
        unset($new->headerNames[$normalized]);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }

        $query = $this->uri->getQuery();
        if ($query !== '') {
            $target .= '?' . $query;
        }

        return $target;
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        if ($requestTarget === $this->getRequestTarget()) {
            return $this;
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        if ($method === $this->method) {
            return $this;
        }

        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost) {
            $host = $uri->getHost();
            if ($host !== '') {
                $port = $uri->getPort();
                if ($port !== null) {
                    $host .= ':' . $port;
                }

                if ($new->hasHeader('Host')) {
                    $new = $new->withoutHeader('Host');
                }
                $new = $new->withHeader('Host', $host);
            }
        }

        return $new;
    }
}
