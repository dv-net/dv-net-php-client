<?php

declare(strict_types=1);

namespace DvNet\DvNetClient;

class HashManager
{
    /**
     * @api
     *
     * @param string $clientSignature
     * @param string $clientKey
     * @param array<mixed, mixed> $requestBody
     * @return bool
     */
    public function checkSign(string $clientSignature, string $clientKey, array|object|string $requestBody): bool
    {
        $stringBody = match (gettype($requestBody)) {
            'string' => $requestBody,
            'array', 'object' => json_encode($requestBody),
        };
        $hash = hash('sha256', $stringBody . $clientKey);

        return hash_equals($clientSignature, $hash);
    }
}
