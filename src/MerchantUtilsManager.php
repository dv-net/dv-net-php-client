<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient;

class MerchantUtilsManager
{
    /**
     * @api
     *
     * @param string $clientSignature
     * @param string $clientKey
     * @param array<mixed, mixed> $requestBody
     *
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

    public function generateLink(string $host, string $storeUuid, string $clientId, string $email): string
    {
        return $host . '/' . $storeUuid . '/' . $clientId . '?' . http_build_query(['email' => $email]);
    }
}
