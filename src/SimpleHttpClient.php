<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient;

use DvNet\DvNetClient\Exceptions\DvNetException;
use DvNet\DvNetClient\SimpleHttp\Response;
use DvNet\DvNetClient\SimpleHttp\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SimpleHttpClient implements ClientInterface
{
    /** @var array<string, mixed> */
    private array $options;

    /** @param  array<string, mixed> $options */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'follow_redirects' => false,
            'verify_peer' => true,
            'timeout' => 30,
        ], $options);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri();
        $headers = $request->getHeaders();
        $body = (string) $request->getBody();

        // Validate request target and method
        if ($method === '') {
            throw new DvNetException('HTTP method cannot be empty');
        }

        // Prepare headers
        $headerLines = [];
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $headerLines[] = "$name: $value";
            }
        }

        // Build stream context
        $httpOptions = [
            'method' => $method,
            'header' => $headerLines,
            'content' => $body,
            'follow_location' => match ($this->options['follow_redirects']) {
                true => 1,
                false => 0,
                default => throw new DvNetException('follow_location option must be bool, ' . gettype($this->options['follow_redirects']) . ' given'),
            },
            'ignore_errors' => true, // Capture 4xx/5xx responses
            'timeout' => $this->options['timeout'],
        ];

        if ($uri->getScheme() === 'https') {
            $httpOptions['ssl'] = [
                'verify_peer' => $this->options['verify_peer'],
                'verify_peer_name' => $this->options['verify_peer'],
            ];
        }

        $context = stream_context_create(['http' => $httpOptions]);
        $url = (string) $uri;

        // Send request
        $stream = @fopen($url, 'r', false, $context);
        if ($stream === false) {
            $error = error_get_last();
            throw new DvNetException($error['message'] ?? 'Request failed');
        }

        // Parse response
        $responseHeaders = $http_response_header;
        $responseBody = stream_get_contents($stream);
        if ($responseBody === false) {
            throw new DvNetException('Failed to get content from response stream');
        }
        fclose($stream);

        // Extract status code and headers
        $statusLine = array_shift($responseHeaders);
        $statusCode = 200;
        $reasonPhrase = '';
        $protocolVersion = '1.1';

        if ($statusLine !== null && preg_match('/^HTTP\/(\d+\.\d+)\s+(\d+)\s+(.*)$/', $statusLine, $matches) !== false) {
            $protocolVersion = $matches[1];
            $statusCode = (int) $matches[2];
            $reasonPhrase = $matches[3];
        }

        $parsedHeaders = [];
        foreach ($responseHeaders as $header) {
            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);
                $parsedHeaders[$name][] = $value;
            }
        }

        // Create response
        $bodyStream = new Stream(fopen('php://temp', 'r+'));
        $bodyStream->write($responseBody);
        $bodyStream->rewind();

        return new Response(
            $statusCode,
            $bodyStream,
            $parsedHeaders,
            $protocolVersion,
            $reasonPhrase,
        );
    }
}
