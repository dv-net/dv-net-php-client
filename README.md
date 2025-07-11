# DV Net Client

A PHP client for DV Net API integration.

# Extended documentation 

You can find extended documentation at https://docs.dv.net/

## Installation
```bash
composer require dv-net/dv-net-php-client
```

## Setup
Initialize the client with your API configuration:

```php
// Create HTTP client (you can use the built-in SimpleHttpClient or your own implementation)
$httpClient = new SimpleHttpClient();

// Initialize the merchant client with your API host
$client = new MerchantClient(
    httpClient: $httpClient,
    host: 'https://api.example.com' // Your DV Net API host
);

// Alternatively, you can pass the host in each request:
$client = new MerchantClient(
    httpClient: $psrHttpClient,
    xApiKey: 'your-api-key',
    host: 'https://api.example.com'
);
```

## Usage

### Signature Verification
Verify the authenticity of request signatures:

```php
$merchantUtilsManager = new MerchantUtilsManager();
$isValid = $merchantUtilsManager->checkSign(
    clientSignature: 'received-signature-hash',
    clientKey: 'your-client-key',
    requestBody: ['data' => 'request-payload']
);
// Returns boolean indicating if the signature is valid
```

### Exchange Balances
Get the total exchange balances across all currencies:

```php
$client = new MerchantClient($httpClient, $host);
$response = $client->getExchangeBalances(
    xApiKey: 'your-api-key'
);
// Returns TotalExchangeBalanceResponse object with total USD value and individual currency balances
```

### External Wallet
Create or retrieve an external wallet for a user:

```php
$response = $client->getExternalWallet(
    xApiKey: 'your-api-key',
    email: 'user@example.com',
    ip: '127.0.0.1',
    storeExternalId: 'store-123',
    amount: '100.00',
    currency: 'USD'
);
// Returns ExternalAddressesResponse object with wallet details and payment URL
```

### Processing Wallets Balances
Get balances for all processing wallets:

```php
$balances = $client->getProcessingWalletsBalances(
    xApiKey: 'your-api-key'
);
// Returns ProcessingWalletBalancesResponse object with detailed balance information
```

### Store Currencies
Get list of available currencies for the store:

```php
$currencies = $client->getStoreCurrencies(
    xApiKey: 'your-api-key'
);
// Returns CurrenciesResponse object with detailed currency information
```

### Store Currency Rate
Get current exchange rate for a specific currency:

```php
$rate = $client->getStoreCurrencyRate(
    xApiKey: 'your-api-key',
    currencyId: 'BTC'
);
// Returns CurrencyRate object with current rate and source information
```

### Withdrawal Processing Status
Check the status of a withdrawal:

```php
$status = $client->getWithdrawalProcessingStatus(
    xApiKey: 'your-api-key',
    withdrawalId: 'withdrawal-123'
);
// Returns ProcessingWithdrawal object with detailed withdrawal status
```

### Initialize Transfer
Initialize a new withdrawal transfer:

```php
$withdrawal = $client->initializeTransfer(
    xApiKey: 'your-api-key',
    addressTo: '0x123...',
    currencyId: 'ETH',
    amount: '1.5'
);
// Returns WithdrawalResponse object with transfer details
```

### Webhook Processing
Process incoming webhooks:

```php
$mapper = new WebhookMapper();
$webhook = $mapper->mapWebhook($rawWebhookData);
// Returns either ConfirmedWebhook or UnconfirmedWebhook object
```