<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class DvNetServerException extends DvNetRuntimeException implements RequestExceptionInterface
{
    private RequestInterface $request;

    public function __construct(string $message, RequestInterface $request, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
