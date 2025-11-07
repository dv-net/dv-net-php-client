<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient;

use DvNet\DvNetClient\Dto\MerchantClient\Dto\AccountDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\UnconfirmedTransactionDto;
use DvNet\DvNetClient\Dto\MerchantClient\Response\CurrenciesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\CurrencyRateResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ExternalAddressesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ProcessingWalletsBalancesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ProcessingWithdrawalResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\TotalExchangeBalanceResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\WithdrawalResponse;
use DvNet\DvNetClient\Exceptions\DvNetException;
use DvNet\DvNetClient\Exceptions\DvNetInvalidRequestException;
use DvNet\DvNetClient\Exceptions\DvNetInvalidResponseDataException;
use DvNet\DvNetClient\Exceptions\DvNetNetworkException;
use DvNet\DvNetClient\Exceptions\DvNetRequestExceptions;
use DvNet\DvNetClient\Exceptions\DvNetServerException;
use DvNet\DvNetClient\Exceptions\DvNetUndefinedHostException;
use DvNet\DvNetClient\Exceptions\DvNetUndefinedXApiKeyException;
use DvNet\DvNetClient\SimpleHttp\Request;
use DvNet\DvNetClient\SimpleHttp\Stream;
use DvNet\DvNetClient\SimpleHttp\Uri;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @psalm-import-type ExchangeBalances from MerchantMapper
 * @psalm-import-type ExternalWallet from MerchantMapper
 * @psalm-import-type ProcessingWalletBalance from MerchantMapper
 * @psalm-import-type Currency from MerchantMapper
 * @psalm-import-type CurrencyRate from MerchantMapper
 * @psalm-import-type ProcessingWithdrawal from MerchantMapper
 * @psalm-import-type Withdrawal from MerchantMapper
 * @psalm-import-type Account from MerchantMapper
 * @psalm-import-type UnconfirmedTransaction from MerchantMapper
 */
class MerchantClient
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly MerchantMapper $merchantMapper,
        private readonly ?string $host = null,
        private readonly ?string $xApiKey = null,
    ) {
    }

    /**
     * @throws DvNetException
     *
     * @api
     */
    public function getExchangeBalances(
        ?string $xApiKey = null,
        ?string $host = null,
    ): TotalExchangeBalanceResponse {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'GET',
            uri: $host . '/api/v1/external/exchange-balances',
            headers: ['x-api-key' => $xApiKey],
        );

        /** @var ExchangeBalances $data */
        return $this->merchantMapper->makeExchangeBalances($data);
    }

    /**
     * @throws DvNetException
     *
     * @api
     */
    public function getExternalWallet(
        string $storeExternalId,
        ?string $email = null,
        ?string $ip = null,
        ?string $amount = null,
        ?string $currency = null,
        ?string $xApiKey = null,
        ?string $host = null,
    ): ExternalAddressesResponse {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'POST',
            uri: $host . '/api/v1/external/wallet',
            data: array_filter(array: [
                'email' => $email,
                'ip' => $ip,
                'store_external_id' => $storeExternalId,
                'amount' => $amount,
                'currency' => $currency,
            ], callback: fn (mixed $elem) => $elem !== null),
            headers: [
                'Content-Type' => 'application/json',
                'x-api-key' => $xApiKey,
            ],
        );

        /** @var ExternalWallet $data */
        return $this->merchantMapper->makeExternalWallet($data);
    }

    /**
     * @throws DvNetException
     *
     * @api
     */
    public function getProcessingWalletsBalances(
        ?string $xApiKey = null,
        ?string $host = null,
    ): ProcessingWalletsBalancesResponse {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'GET',
            uri: $host . '/api/v1/external/processing-wallet-balances',
            headers: ['x-api-key' => $xApiKey],
        );

        /** @var ProcessingWalletBalance[] $data */
        return $this->merchantMapper->makeProcessingWalletsBalances($data);
    }

    /**
     * @throws DvNetException
     *
     * @api
     */
    public function getStoreCurrencies(
        ?string $xApiKey = null,
        ?string $host = null,
    ): CurrenciesResponse {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'GET',
            uri: $host . '/api/v1/external/store/currencies',
            headers: ['x-api-key' => $xApiKey],
        );

        /** @var Currency[] $data */
        return $this->merchantMapper->makeCurrencies($data);
    }

    /**
     * @throws DvNetException
     *
     * @api
     */
    public function getStoreCurrencyRate(
        string $currencyId,
        ?string $xApiKey = null,
        ?string $host = null,
    ): CurrencyRateResponse {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'GET',
            uri: $host . "/api/v1/external/store/currencies/{$currencyId}/rate",
            headers: ['x-api-key' => $xApiKey],
        );

        /** @var CurrencyRate $data */
        return $this->merchantMapper->makeCurrencyRate($data);
    }

    /**
     * @throws DvNetException
     *
     * @api
     */
    public function getWithdrawalProcessingStatus(
        string $withdrawalId,
        ?string $xApiKey = null,
        ?string $host = null,
    ): ProcessingWithdrawalResponse {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'GET',
            uri: $host . "/api/v1/external/withdrawal-from-processing/{$withdrawalId}",
            headers: ['x-api-key' => $xApiKey],
        );

        /** @var ProcessingWithdrawal $data */
        return $this->merchantMapper->makeProcessingWithdrawal($data);
    }

    /**
     * @throws DvNetException
     *
     * @api
     */
    public function initializeTransfer(
        string $addressTo,
        string $currencyId,
        string $amount,
        string $requestId,
        ?string $xApiKey = null,
        ?string $host = null,
    ): WithdrawalResponse {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'POST',
            uri: $host . '/api/v1/external/withdrawal-from-processing',
            data: [
                'address_to' => $addressTo,
                'currency_id' => $currencyId,
                'amount' => $amount,
                'request_id' => $requestId,
            ],
            headers: [
                'Content-Type' => 'application/json',
                'x-api-key' => $xApiKey,
            ],
        );

        /** @var Withdrawal $data */
        return $this->merchantMapper->makeWithdrawal($data);
    }

    /**
     * @throws DvNetException
     *
     * @return AccountDto[]
     */
    public function getHotWalletBalances(?string $xApiKey = null, ?string $host = null): array
    {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'GET',
            uri: $host . '/api/v1/external/wallet/balance/hot',
            headers: [
                'Content-Type' => 'application/json',
                'x-api-key' => $xApiKey,
            ],
        );

        /** @var Account[] $data */
        return array_map(callback: [$this->merchantMapper, 'makeAccount'], array: $data);
    }

    /**
     * @throws DvNetException
     */
    public function deleteWithdrawalFromProcessing(string $id, ?string $xApiKey = null, ?string $host = null): void
    {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $this->sendRequest(
            method: 'DELETE',
            uri: $host . '/api/v1/external/withdrawal-from-processing/' . $id,
            headers: ['x-api-key' => $xApiKey],
        );
    }

    /**
     * @return UnconfirmedTransactionDto[]
     */
    public function getUnconfirmedTransactions(?string $xApiKey = null, ?string $host = null): array
    {
        [$host, $xApiKey] = $this->getActualRequestParams(xApiKey: $xApiKey, host: $host);
        $data = $this->sendRequest(
            method: 'GET',
            uri: $host . '/api/v1/external/transactions/unconfirmed/transfer',
            headers: ['x-api-key' => $xApiKey],
        );

        /** @var UnconfirmedTransaction[] $data */
        return array_map(callback: [$this->merchantMapper, 'makeUnconfirmedTransfer'], array: $data);
    }

    /**
     * @param array<string, string> $headers
     * @param mixed[] $data
     *
     * @throws DvNetException
     */
    private function sendRequest(string $method, string $uri, ?array $data = null, array $headers = []): mixed
    {
        try {
            if ($data !== null) {
                $requestBody = json_encode(value: (object) $data, flags: JSON_THROW_ON_ERROR);
                $stream = new Stream(fopen(filename: 'php://temp', mode: 'r+'));
                $stream->write($requestBody);
            }

            $request = new Request(method: $method, uri: new Uri($uri), headers: $headers, body: $stream ?? null);
        } catch (Throwable $exception) {
            throw new DvNetInvalidRequestException('Failed to form request', previous: $exception);
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (NetworkExceptionInterface $exception) {
            throw new DvNetNetworkException(
                message: 'Network error, got response: ' . $exception->getMessage() . ' and code ' . $exception->getCode(),
                request: $request,
                code: $exception->getCode(),
                previous: $exception,
            );
        } catch (RequestExceptionInterface $exception) {
            throw new DvNetRequestExceptions(
                message: 'Request error, got response: ' . $exception->getMessage() . ' and code ' . $exception->getCode(),
                request: $request,
                code: $exception->getCode(),
                previous: $exception,
            );
        } catch (ClientExceptionInterface $exception) {
            throw new DvNetServerException(
                message: 'Client error, got response: ' . $exception->getMessage() . ' and code ' . $exception->getCode(),
                request: $request,
                code: $exception->getCode(),
                previous: $exception,
            );
        }

        $this->checkOkResponse(response: $response, request: $request);

        try {
            $json = json_decode(json: (string) $response->getBody(), associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new DvNetInvalidResponseDataException('Invalid json.', previous: $exception);
        }

        if (!is_array($json) || !array_key_exists('data', $json)) {
            throw new DvNetInvalidResponseDataException('The response does not contain an array of data.');
        }

        return $json['data'];
    }

    /**
     * @return array{string, string}
     *
     * @throws DvNetUndefinedHostException
     * @throws DvNetUndefinedXApiKeyException
     */
    private function getActualRequestParams(?string $xApiKey, ?string $host): array
    {
        return [
            $host ?? $this->host ?? throw new DvNetUndefinedHostException('Please set host in client, or pass it in parameters'),
            $xApiKey ?? $this->xApiKey ?? throw new DvNetUndefinedXApiKeyException('Please set x-api-key in client, or pass it in parameters'),
        ];
    }

    private function checkOkResponse(ResponseInterface $response, Request $request): void
    {
        match (true) {
            $response->getStatusCode() === 200 => null,
            $response->getStatusCode() >= 400 && $response->getStatusCode() < 500 => throw new DvNetNetworkException(
                message: 'Client error, got response: ' . $response->getBody()->getContents() . ' and code ' . $response->getStatusCode(),
                request: $request,
                code: $response->getStatusCode(),
            ),
            $response->getStatusCode() >= 500 => throw new DvNetServerException(
                message: 'Server error, got response: ' . $response->getBody()->getContents() . ' and code ' . $response->getStatusCode(),
                request: $request,
                code: $response->getStatusCode(),
            ),
            default => true,
        };
    }
}
