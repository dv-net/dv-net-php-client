<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Exceptions;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class DvNetNetworkException extends DvNetRuntimeException implements NetworkExceptionInterface
{
    private RequestInterface $request;

    public function __construct(string $message, RequestInterface $request, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
