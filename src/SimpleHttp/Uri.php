<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\SimpleHttp;

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

class Uri implements UriInterface
{
    private string $scheme   = '';
    private string $userInfo = '';
    private string $host     = '';
    private ?int $port       = null;
    private string $path     = '';
    private string $query    = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $this->parseUri($uri);
        }
    }

    private function parseUri(string $uri): void
    {
        $components = parse_url($uri);

        if ($components === false) {
            throw new InvalidArgumentException('Unable to parse URI: ' . $uri);
        }

        $this->scheme   = isset($components['scheme']) ? strtolower($components['scheme']) : '';
        $this->host     = isset($components['host']) ? strtolower($components['host']) : '';
        $this->port     = $components['port'] ?? null;
        $this->path     = $components['path'] ?? '';
        $this->query    = $components['query'] ?? '';
        $this->fragment = $components['fragment'] ?? '';
        $this->userInfo = $components['user'] ?? '';
        if (isset($components['pass'])) {
            $this->userInfo .= ':' . $components['pass'];
        }
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        $authority = $this->host;

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $scheme = strtolower($scheme);
        if ($scheme === $this->scheme) {
            return $this;
        }

        $new         = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $userInfo = $user;
        if ($password !== null) {
            $userInfo .= ':' . $password;
        }

        if ($userInfo === $this->userInfo) {
            return $this;
        }

        $new           = clone $this;
        $new->userInfo = $userInfo;

        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        $host = strtolower($host);
        if ($host === $this->host) {
            return $this;
        }

        $new       = clone $this;
        $new->host = $host;

        return $new;
    }

    public function withPort(?int $port): UriInterface
    {
        if ($port === $this->port) {
            return $this;
        }

        $new       = clone $this;
        $new->port = $port;

        return $new;
    }

    public function withPath(string $path): UriInterface
    {
        if ($path === $this->path) {
            return $this;
        }

        $new       = clone $this;
        $new->path = $path;

        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        if ($query === $this->query) {
            return $this;
        }

        $new        = clone $this;
        $new->query = $query;

        return $new;
    }

    public function withFragment(string $fragment): UriInterface
    {
        if ($fragment === $this->fragment) {
            return $this;
        }

        $new           = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        $uri .= $this->path;

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
