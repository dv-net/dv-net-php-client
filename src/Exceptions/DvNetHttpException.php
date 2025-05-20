<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Exceptions;

use DvNet\DvNetClient\Exceptions\DvNetRuntimeException;

class DvNetHttpException extends DvNetRuntimeException
{
    private string $responseBody;

    /**
     * @var string[]
     */
    private array $responseHeaders;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $message,
        int $code,
        string $responseBody,
        array $headers
    ) {
        parent::__construct($message, $code);
        $this->responseBody    = $responseBody;
        $this->responseHeaders = $headers;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * @return array<string, string>
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }
}
