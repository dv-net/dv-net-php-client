<?php

declare(strict_types=1);

namespace DvNet\DvNetClient;

use DateTimeImmutable;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\AccountDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\AddressDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\AssetDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\BalanceDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\BlockchainAdditionalDataDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\CurrencyDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\CurrencyShortDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\ExchangeBalanceDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\ProcessingWalletBalanceDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\TransferDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\TronDataDto;
use DvNet\DvNetClient\Dto\MerchantClient\Response\CurrenciesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\CurrencyRateResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ExternalAddressesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ProcessingWalletsBalancesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ProcessingWithdrawalResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\TotalExchangeBalanceResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\WithdrawalResponse;
use DvNet\DvNetClient\Exceptions\DvNetException;
use DvNet\DvNetClient\Exceptions\DvNetMissingFieldInResponseException;
use DvNet\DvNetClient\Exceptions\DvNetUndefinedHostException;
use DvNet\DvNetClient\Exceptions\DvNetUndefinedXApiKeyException;
use DvNet\DvNetClient\SimpleHttp\Request;
use DvNet\DvNetClient\SimpleHttp\Stream;
use DvNet\DvNetClient\SimpleHttp\Uri;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\IconDto;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class MerchantClient
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly ?string $host = null,
        private readonly ?string $xApiKey = null,
    ) {
    }

    /**
     * @throws DvNetUndefinedHostException
     * @api
     *
     */
    public function getExchangeBalances(
        ?string $xApiKey = null,
        ?string $host = null,
    ): TotalExchangeBalanceResponse {
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $request = new Request(
            'GET',
            new Uri($host . '/api/v1/external/exchange-balances'),
            [
                'x-api-key' => $xApiKey,
            ],
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);

        $json = json_decode($response->getBody()->getContents(), true)['data'];

        return new TotalExchangeBalanceResponse(
            $json['total_usd'],
            array_map(function (array $data) {
                return new ExchangeBalanceDto(
                    $data['amount'],
                    $data['amount_usd'],
                    $data['currency'],
                );
            }, $json['balances']),
        );
    }

    /**
     * @throws DvNetException
     * @throws DvNetUndefinedHostException
     * @api
     *
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
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $requestBody = json_encode((object) array_filter([
            'email'             => $email,
            'ip'                => $ip,
            'store_external_id' => $storeExternalId,
            'amount'            => $amount,
            'currency'          => $currency,
        ], fn (mixed $elem) => is_null($elem) === false), JSON_THROW_ON_ERROR);
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($requestBody);

        $request = new Request(
            'POST',
            new Uri($host . '/api/v1/external/wallet'),
            [
                'Content-Type' => 'application/json',
                'x-api-key'    => $xApiKey,
            ],
            $stream,
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);

        $json = json_decode($response->getBody()->getContents(), true)['data'];

        $addresses = array_map(
            fn (array $addressData) => new AddressDto(
                $addressData['id'],
                $addressData['wallet_id'],
                $addressData['user_id'],
                $addressData['currency_id'],
                $addressData['blockchain'],
                $addressData['address'],
                $addressData['dirty'],
            ),
            $json['address'],
        );

        return new ExternalAddressesResponse(
            $addresses,
            new DateTimeImmutable($json['created_at']),
            $json['id'],
            $json['pay_url'],
            $json['store_external_id'],
            $json['store_id'],
            new DateTimeImmutable($json['updated_at']),
            $json['rates'],
            $json['amount_usd'],
        );
    }

    /**
     * @return ProcessingWalletsBalancesResponse
     * @throws DvNetUndefinedHostException
     * @api
     */
    public function getProcessingWalletsBalances(
        ?string $xApiKey = null,
        ?string $host = null,
    ): ProcessingWalletsBalancesResponse {
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $request = new Request(
            'GET',
            new Uri($host . '/api/v1/external/processing-wallet-balances'),
            [
                'x-api-key' => $xApiKey,
            ],
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);
        $json     = json_decode($response->getBody()->getContents(), true);
        $balances = [];
        foreach ($json['data'] as $balance) {
            $balances[] = $this->mapProcessingWalletBalanceResponse($balance);
        }

        return new ProcessingWalletsBalancesResponse($balances);
    }

    /**
     * @param array<string, mixed> $data
     * @return ProcessingWalletBalanceDto
     */
    private function mapProcessingWalletBalanceResponse(array $data): ProcessingWalletBalanceDto
    {
        $data['assets'] ?? throw new DvNetMissingFieldInResponseException('assets');
        $data['currency'] ?? throw new DvNetMissingFieldInResponseException('currency');
        $data['balance'] ?? throw new DvNetMissingFieldInResponseException('balance');
        $data['address'] ?? throw new DvNetMissingFieldInResponseException('address');
        $data['blockchain'] ?? throw new DvNetMissingFieldInResponseException('blockchain');

        $assets = array_map(
            fn (array $assetData) => new AssetDto(
                $assetData['identity'],
                $assetData['amount'],
                $assetData['amount_usd'],
            ),
            $data['assets'],
        );

        $currency = new CurrencyShortDto(
            $data['currency']['id'],
            $data['currency']['code'],
            $data['currency']['name'],
            $data['currency']['blockchain'],
        );

        $balance = new BalanceDto(
            $data['balance']['native_token'],
            $data['balance']['native_token_usd'],
        );

        $additionalData = null;
        if (isset($data['additional_data']['tron_data'])) {
            $tronData       = $data['additional_data']['tron_data'];
            $additionalData = new BlockchainAdditionalDataDto(
                new TronDataDto(
                    $tronData['available_bandwidth_for_use'],
                    $tronData['available_energy_for_use'],
                    $tronData['stacked_bandwidth'],
                    $tronData['stacked_bandwidth_trx'],
                    $tronData['stacked_energy'],
                    $tronData['stacked_energy_trx'],
                    $tronData['stacked_trx'],
                    $tronData['total_bandwidth'],
                    $tronData['total_energy'],
                    $tronData['total_used_bandwidth'],
                    $tronData['total_used_energy'],
                ),
            );
        }

        return new ProcessingWalletBalanceDto(
            $data['address'],
            $data['blockchain'],
            $assets,
            $currency,
            $balance,
            $additionalData,
        );
    }


    /**
     * @throws DvNetUndefinedHostException
     * @api
     *
     */
    public function getStoreCurrencies(
        ?string $xApiKey = null,
        ?string $host = null,
    ): CurrenciesResponse {
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $request = new Request(
            'GET',
            new Uri($host . '/api/v1/external/store/currencies'),
            [
                'x-api-key' => $xApiKey,
            ],
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);
        $json = json_decode($response->getBody()->getContents(), true);

        $currencies = [];
        foreach ($json['data'] as $data) {
            $currencies[] = new CurrencyDto(
                $data['id'],
                $data['blockchain'],
                $data['code'],
                $data['contract_address'],
                $data['has_balance'],
                new IconDto(
                    $data['icon']['icon_128'],
                    $data['icon']['icon_512'],
                    $data['icon']['icon_svg'],
                ),
                $data['is_fiat'],
                $data['min_confirmation'],
                $data['name'],
                $data['precision'],
                $data['status'],
                $data['withdrawal_min_balance'],
                $data['explorer_link'],
            );
        }

        return new CurrenciesResponse($currencies);
    }

    /**
     * @throws DvNetUndefinedHostException
     * @api
     *
     */
    public function getStoreCurrencyRate(
        string $currencyId,
        ?string $xApiKey = null,
        ?string $host = null,
    ): CurrencyRateResponse {
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $request = new Request(
            'GET',
            new Uri($host . "/api/v1/external/store/currencies/{$currencyId}/rate"),
            [
                'x-api-key' => $xApiKey,
            ],
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);
        $json = json_decode($response->getBody()->getContents(), true)['data'];

        return new CurrencyRateResponse(
            $json['code'],
            $json['rate'],
            $json['rate_source'],
        );
    }

    /**
     * @api
     */
    public function getWithdrawalProcessingStatus(
        string $withdrawalId,
        ?string $xApiKey = null,
        ?string $host = null,
    ): ProcessingWithdrawalResponse {
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $request = new Request(
            'GET',
            new Uri($host . "/api/v1/external/withdrawal-from-processing/{$withdrawalId}"),
            [
                'x-api-key' => $xApiKey,
            ],
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);
        $json        = json_decode($response->getBody()->getContents(), true)['data'];
        $transferDto = null;
        if (isset(
            $json['transfer']['kind'],
            $json['transfer']['stage'],
            $json['transfer']['status'],
        )) {
            $transferDto = new TransferDto(
                $json['transfer']['kind'],
                $json['transfer']['stage'],
                $json['transfer']['status'],
            );
        }

        return new ProcessingWithdrawalResponse(
            $json['address_from'],
            $json['address_to'],
            $json['amount'],
            $json['amount_usd'],
            new DateTimeImmutable($json['created_at']),
            $json['currency_id'],
            $json['store_id'],
            $transferDto,
            $json['tx_hash'],
        );
    }

    /**
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
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $requestBody = json_encode((object) [
            'address_to'  => $addressTo,
            'currency_id' => $currencyId,
            'amount'      => $amount,
            'request_id'  => $requestId,
        ], JSON_THROW_ON_ERROR);
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write($requestBody);
        $request = new Request(
            'POST',
            new Uri($host . '/api/v1/external/withdrawal-from-processing'),
            [
                'Content-Type' => 'application/json',
                'x-api-key'    => $xApiKey,
            ],
            $stream,
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);
        $json = json_decode($response->getBody()->getContents(), true)['data'];

        return new WithdrawalResponse(
            $json['address_from'],
            $json['address_to'],
            $json['amount'],
            $json['amount_usd'],
            new DateTimeImmutable($json['created_at']),
            $json['currency_id'],
            $json['id'],
            $json['store_id'],
            $json['transfer_id'] ?? null,
        );
    }


    /**
     * @return AccountDto[]
     */
    public function getHotWalletBalances(?string $xApiKey = null, ?string $host = null): array
    {
        list($host, $xApiKey) = $this->getActualRequestParams(
            xApiKey: $xApiKey,
            host: $host,
        );

        $request = new Request(
            'GET',
            new Uri($host . '/api/v1/external/wallet/balance/hot'),
            [
                'Content-Type' => 'application/json',
                'x-api-key'    => $xApiKey,
            ],
        );
        $response = $this->httpClient->sendRequest($request);
        $this->checkOkResponse($response);

        $json = json_decode($response->getBody()->getContents(), true)['data'];

        return array_map(function (array $data) {
            return new AccountDto(
                $data['balance'],
                $data['balance_usd'],
                $data['count'],
                $data['count_with_balance'],
                new CurrencyShortDto(
                    $data['currency']['blockchain'],
                    $data['currency']['code'],
                    $data['currency']['id'],
                    $data['currency']['name'],
                ),
            );
        }, $json);
    }

    /**
     * @return array{string, string}
     * @throws DvNetUndefinedHostException
     * @throws DvNetUndefinedXApiKeyException
     */
    private function getActualRequestParams(?string $xApiKey, ?string $host): array
    {
        return [
            $host ?? $this->host ?? throw new DvNetUndefinedHostException("Please set host in client, or pass it in parameters"),
            $xApiKey ?? $this->xApiKey ?? throw new DvNetUndefinedXApiKeyException("Please set x-api-key in client, or pass it in parameters"),
        ];
    }

    private function checkOkResponse(ResponseInterface $response): void
    {
        match (true) {
            $response->getStatusCode() === 200 => null,
            $response->getStatusCode() >= 400 && $response->getStatusCode() < 500
                => throw new DvNetException(
                    'Client error, got response: '.$response->getBody()->getContents(). ' and code '.$response->getStatusCode(),
                    $response->getStatusCode(),
                ),
            $response->getStatusCode() >= 500
            => throw new DvNetException(
                'Server error, got response: '.$response->getBody()->getContents(). ' and code '.$response->getStatusCode(),
                $response->getStatusCode(),
            ),
            default => true,
        };
    }
}
