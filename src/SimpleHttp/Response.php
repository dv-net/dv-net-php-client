<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\SimpleHttp;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    private string $protocolVersion;
    /** @var array<string, mixed>  */
    private array $headers = [];
    /** @var array<string, mixed>  */
    private array $headerNames = [];
    private StreamInterface $body;
    private int $statusCode;
    private string $reasonPhrase;

    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        int $statusCode = 200,
        ?StreamInterface $body = null,
        array $headers = [],
        string $protocolVersion = '1.1',
        string $reasonPhrase = ''
    ) {
        $this->statusCode      = $statusCode;
        $this->body            = $body ?? new Stream(fopen('php://temp', 'r+'));
        $this->protocolVersion = $protocolVersion;
        $this->reasonPhrase    = $reasonPhrase;

        foreach ($headers as $name => $value) {
            $value      = (array) $value;
            $normalized = strtolower($name);
            if (isset($this->headerNames[$normalized])) {
                $originalName = $this->headerNames[$normalized];
                unset($this->headers[$originalName]);
            }
            $this->headerNames[$normalized] = $name;
            $this->headers[$name]           = $value;
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

        $new                  = clone $this;
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
        $value      = (array) $value;
        $normalized = strtolower($name);

        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $originalName = $new->headerNames[$normalized];
            unset($new->headers[$originalName]);
        }

        $new->headerNames[$normalized] = $name;
        $new->headers[$name]           = $value;

        return $new;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $value      = (array) $value;
        $normalized = strtolower($name);

        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $originalName                = $new->headerNames[$normalized];
            $new->headers[$originalName] = array_merge($new->headers[$originalName], $value);
        } else {
            $new->headerNames[$normalized] = $name;
            $new->headers[$name]           = $value;
        }

        return $new;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $normalized = strtolower($name);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $new          = clone $this;
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

        $new       = clone $this;
        $new->body = $body;

        return $new;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        if ($code === $this->statusCode && $reasonPhrase === $this->reasonPhrase) {
            return $this;
        }

        $new               = clone $this;
        $new->statusCode   = $code;
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
