<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient;

use DateTimeImmutable;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\AccountDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\AddressDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\AssetDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\BalanceDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\BlockchainAdditionalDataDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\BlockchainDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\CurrencyDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\CurrencyShortDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\ExchangeBalanceDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\ExtendedCurrencyDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\IconDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\ProcessingWalletBalanceDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\TokenDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\TransferDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\TronDataDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\UnconfirmedTransactionDto;
use DvNet\DvNetClient\Dto\MerchantClient\Response\CurrenciesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\CurrencyRateResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ExtendedCurrenciesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ExternalAddressesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ProcessingWalletsBalancesResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\ProcessingWithdrawalResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\TotalExchangeBalanceResponse;
use DvNet\DvNetClient\Dto\MerchantClient\Response\WithdrawalResponse;
use DvNet\DvNetClient\Exceptions\DvNetInvalidResponseDataException;
use Throwable;

/**
 * @psalm-type Account = array{
 *       balance: string,
 *       balance_usd: string,
 *       count: int,
 *       count_with_balance: int,
 *       currency: CurrencyShort,
 *   }
 * @psalm-type CurrencyShort = array{
 *       id: string,
 *       code: string,
 *       name: string,
 *       blockchain: string,
 *   }
 * @psalm-type Withdrawal = array{
 *      address_from: string,
 *      address_to: string,
 *      amount: string,
 *      amount_usd: string,
 *      created_at: string,
 *      currency_id: string,
 *      id: string,
 *      store_id: string,
 *      transfer_id?: string,
 *  }
 * @psalm-type ExchangeBalances = array{total_usd: string, balances: ExchangeBalance[]}
 * @psalm-type ExchangeBalance = array{amount: string, amount_usd: string, currency: string}
 * @psalm-type ExternalWallet = array{
 *      address: Address[],
 *      created_at: string,
 *      id: string,
 *      pay_url: string,
 *      store_external_id: string,
 *      store_id: string,
 *      updated_at: string,
 *      rates: string[],
 *      amount_usd: string,
 *  }
 * @psalm-type Address = array{
 *      id: string,
 *      wallet_id: string,
 *      user_id: string,
 *      currency_id: string,
 *      blockchain: string,
 *      address: string,
 *      dirty: bool,
 *  }
 * @psalm-type ProcessingWalletBalance = array{
 *      address: string,
 *      blockchain: string,
 *      assets: Asset[],
 *      currency: CurrencyShort,
 *      balance: Balance,
 *      additional_data?: array{
 *          tron_data?: BlockchainAdditionalData,
 *      },
 *  }
 * @psalm-type Asset = array{
 *      identity: string,
 *      amount: string,
 *      amount_usd: string,
 *  }
 * @psalm-type Balance = array{native_token: string, native_token_usd: string}
 * @psalm-type BlockchainAdditionalData = array{
 *      available_bandwidth_for_use: string,
 *      available_energy_for_use: string,
 *      stacked_bandwidth: string,
 *      stacked_bandwidth_trx: string,
 *      stacked_energy: string,
 *      stacked_energy_trx: string,
 *      stacked_trx: string,
 *      total_bandwidth: string,
 *      total_energy: string,
 *      total_used_bandwidth: string,
 *      total_used_energy: string,
 *  }
 * @psalm-type Currency = array{
 *      id: string,
 *      blockchain: string,
 *      code: string,
 *      contract_address: string,
 *      has_balance: bool,
 *      icon: Icon,
 *      blockchain_icon: Icon,
 *      is_fiat: bool,
 *      min_confirmation: int,
 *      name: string,
 *      precision: int,
 *      status: bool,
 *      withdrawal_min_balance: string,
 *      explorer_link: string,
 *  }
 * @psalm-type ExtendedCurrency = array{
 *      id: string,
 *      code: string,
 *      name: string,
 *      blockchain: string,
 *      contract_address: string,
 *      has_balance: bool,
 *      min_confirmation: int,
 *      icon: Icon,
 *      token_icon: Icon,
 *      blockchain_icon: Icon,
 *      explorer_link: string,
 *  }
 * @psalm-type Blockchain = array{
 *      name: string,
 *      icon: Icon,
 *      currencies: string[],
 *      tokens: string[],
 *  }
 * @psalm-type Token = array{
 *      name: string,
 *      icon: Icon,
 *      currencies: string[],
 *      blockchains: string[],
 *  }
 * @psalm-type Icon = array{
 *      icon_128: string,
 *      icon_512: string,
 *      icon_svg: string,
 *  }
 * @psalm-type CurrencyRate = array{
 *      code: string,
 *      rate: string,
 *      rate_source: string,
 *  }
 * @psalm-type ProcessingWithdrawal = array{
 *      address_from: string,
 *      address_to: string,
 *      amount: string,
 *      amount_usd: string,
 *      created_at: string,
 *      currency_id: string,
 *      store_id: string,
 *      transfer?: Transfer,
 *      tx_hash: string,
 *  }
 * @psalm-type Transfer = array{
 *      kind: string,
 *      stage: string,
 *      status: string,
 *  }
 *
 * @phpstan-type UnconfirmedTransaction = array{
 *     amount: "0",
 *     amount_usd: "0"|null,
 *     bc_uniq_key: "string",
 *     blockchain: "string",
 *     created_at: "2025-11-07T06:10:49.104Z",
 *     currency_id: "string",
 *     from_address: "string",
 *     id: "3fa85f64-5717-4562-b3fc-2c963f66afa6",
 *     network_created_at: "2025-11-07T06:10:49.104Z",
 *     to_address: "string",
 *     tx_hash: "string",
 *     type: "string",
 *     updated_at: "2025-11-07T06:10:49.104Z"
 * }
 */
class MerchantMapper
{
    /**
     * @param ExchangeBalances $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeExchangeBalances(array $data): TotalExchangeBalanceResponse
    {
        try {
            return new TotalExchangeBalanceResponse(
                totalUsd: $data['total_usd'],
                exchangeBalance: array_map(callback: [$this, 'makeExchangeBalance'], array: $data['balances']),
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param ExchangeBalance $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeExchangeBalance(array $data): ExchangeBalanceDto
    {
        try {
            return new ExchangeBalanceDto(
                amount: $data['amount'],
                amountUsd: $data['amount_usd'],
                currency: $data['currency'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param ExternalWallet $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeExternalWallet(array $data): ExternalAddressesResponse
    {
        try {
            return new ExternalAddressesResponse(
                address: array_map(callback: [$this, 'makeAddress'], array: $data['address']),
                createdAt: new DateTimeImmutable($data['created_at']),
                id: $data['id'],
                payUrl: $data['pay_url'],
                storeExternalId: $data['store_external_id'],
                storeId: $data['store_id'],
                updatedAt: new DateTimeImmutable($data['updated_at']),
                rates: $data['rates'],
                amountUsd: $data['amount_usd'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Address $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeAddress(array $data): AddressDto
    {
        try {
            return new AddressDto(
                id: $data['id'],
                walletId: $data['wallet_id'],
                userId: $data['user_id'],
                currencyId: $data['currency_id'],
                blockchain: $data['blockchain'],
                address: $data['address'],
                dirty: $data['dirty'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param ProcessingWalletBalance[] $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeProcessingWalletsBalances(array $data): ProcessingWalletsBalancesResponse
    {
        try {
            return new ProcessingWalletsBalancesResponse(array_map(callback: [$this, 'makeProcessingWalletBalance'], array: $data));
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param ProcessingWalletBalance $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeProcessingWalletBalance(array $data): ProcessingWalletBalanceDto
    {
        try {
            return new ProcessingWalletBalanceDto(
                address: $data['address'],
                blockchain: $data['blockchain'],
                asset: array_map(callback: [$this, 'makeAsset'], array: $data['assets']),
                currency: $this->makeCurrencyShort($data['currency']),
                balance: $this->makeBalance($data['balance']),
                additionalData: isset($data['additional_data']['tron_data'])
                    ? $this->makeBlockchainAdditionalData($data['additional_data']['tron_data'])
                    : null,
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Asset $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeAsset(array $data): AssetDto
    {
        try {
            return new AssetDto(
                identity: $data['identity'],
                amount: $data['amount'],
                amountUsd: $data['amount_usd'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param CurrencyShort $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeCurrencyShort(array $data): CurrencyShortDto
    {
        try {
            return new CurrencyShortDto(
                id: $data['id'],
                code: $data['code'],
                name: $data['name'],
                blockchain: $data['blockchain'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Balance $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeBalance(array $data): BalanceDto
    {
        try {
            return new BalanceDto(
                nativeToken: $data['native_token'],
                nativeTokenUsd: $data['native_token_usd'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param BlockchainAdditionalData $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeBlockchainAdditionalData(array $data): BlockchainAdditionalDataDto
    {
        try {
            return new BlockchainAdditionalDataDto(
                new TronDataDto(
                    availableBandwidthForUse: $data['available_bandwidth_for_use'],
                    availableEnergyForUse: $data['available_energy_for_use'],
                    stackedBandwidth: $data['stacked_bandwidth'],
                    stackedBandwidthTrx: $data['stacked_bandwidth_trx'],
                    stackedEnergy: $data['stacked_energy'],
                    stackedEnergyTrx: $data['stacked_energy_trx'],
                    stackedTrx: $data['stacked_trx'],
                    totalBandwidth: $data['total_bandwidth'],
                    totalEnergy: $data['total_energy'],
                    totalUsedBandwidth: $data['total_used_bandwidth'],
                    totalUsedEnergy: $data['total_used_energy'],
                ),
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Currency[] $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeCurrencies(array $data): CurrenciesResponse
    {
        try {
            return new CurrenciesResponse(array_map(callback: [$this, 'makeCurrency'], array: $data));
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param array{
     *     tokens: Token[],
     *     blockchains: Blockchain[],
     *     currencies: ExtendedCurrency[]
     * } $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeExtendedCurrencies(array $data): ExtendedCurrenciesResponse
    {
        try {
            return new ExtendedCurrenciesResponse(
                tokens: array_map(callback: [$this, 'makeToken'], array: $data['tokens']),
                blockchains: array_map(callback: [$this, 'makeBlockchain'], array: $data['blockchains']),
                currencies: array_map(callback: [$this, 'makeExtendedCurrency'], array: $data['currencies']),
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Token $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeToken(array $data): TokenDto
    {
        try {
            return new TokenDto(
                name: $data['name'],
                icon: $this->makeIcon($data['icon']),
                currencies: $data['currencies'],
                blockchains: $data['blockchains'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Blockchain $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeBlockchain(array $data): BlockchainDto
    {
        try {
            return new BlockchainDto(
                name: $data['name'],
                icon: $this->makeIcon($data['icon']),
                currencies: $data['currencies'],
                tokens: $data['tokens'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param ExtendedCurrency $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeExtendedCurrency(array $data): ExtendedCurrencyDto
    {
        try {
            return new ExtendedCurrencyDto(
                id: $data['id'],
                code: $data['code'],
                name: $data['name'],
                blockchain: $data['blockchain'],
                contractAddress: $data['contract_address'],
                hasBalance: $data['has_balance'],
                minConfirmation: $data['min_confirmation'],
                icon: $this->makeIcon($data['icon']),
                tokenIcon: $this->makeIcon($data['token_icon']),
                blockchainIcon: $this->makeIcon($data['blockchain_icon']),
                explorerLink: $data['explorer_link'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Currency $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeCurrency(array $data): CurrencyDto
    {
        try {
            return new CurrencyDto(
                id: $data['id'],
                blockchain: $data['blockchain'],
                code: $data['code'],
                contractAddress: $data['contract_address'],
                hasBalance: $data['has_balance'],
                icon: $this->makeIcon($data['icon']),
                blockchainIcon: $this->makeIcon($data['blockchain_icon']),
                isFiat: $data['is_fiat'],
                minConfirmation: $data['min_confirmation'],
                name: $data['name'],
                precision: $data['precision'],
                status: $data['status'],
                withdrawalMinBalance: $data['withdrawal_min_balance'],
                explorerLink: $data['explorer_link'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Icon $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeIcon(array $data): IconDto
    {
        try {
            return new IconDto(
                icon128: $data['icon_128'],
                icon512: $data['icon_512'],
                iconSvg: $data['icon_svg'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param CurrencyRate $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeCurrencyRate(array $data): CurrencyRateResponse
    {
        try {
            return new CurrencyRateResponse(
                code: $data['code'],
                rate: $data['rate'],
                rateSource: $data['rate_source'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param ProcessingWithdrawal $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeProcessingWithdrawal(array $data): ProcessingWithdrawalResponse
    {
        try {
            return new ProcessingWithdrawalResponse(
                addressFrom: $data['address_from'],
                addressTo: $data['address_to'],
                amount: $data['amount'],
                amountUsd: $data['amount_usd'],
                createdAt: new DateTimeImmutable($data['created_at']),
                currencyId: $data['currency_id'],
                storeId: $data['store_id'],
                transfer: isset($data['transfer']) ? $this->makeTransfer($data['transfer']) : null,
                txHash: $data['tx_hash'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Transfer $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeTransfer(array $data): TransferDto
    {
        try {
            return new TransferDto(
                kind: $data['kind'],
                stage: $data['stage'],
                status: $data['status'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Withdrawal $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeWithdrawal(array $data): WithdrawalResponse
    {
        try {
            return new WithdrawalResponse(
                addressFrom: $data['address_from'],
                addressTo: $data['address_to'],
                amount: $data['amount'],
                amountUsd: $data['amount_usd'],
                createdAt: new DateTimeImmutable($data['created_at']),
                currencyId: $data['currency_id'],
                id: $data['id'],
                storeId: $data['store_id'],
                transferId: $data['transfer_id'] ?? null,
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param Account $data
     *
     * @throws DvNetInvalidResponseDataException
     */
    public function makeAccount(array $data): AccountDto
    {
        try {
            return new AccountDto(
                balance: $data['balance'],
                balanceUsd: $data['balance_usd'],
                count: $data['count'],
                countWithBalance: $data['count_with_balance'],
                currency: $this->makeCurrencyShort($data['currency']),
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }

    /**
     * @param UnconfirmedTransaction $data
     *
     * @return UnconfirmedTransactionDto
     */
    public function makeUnconfirmedTransfer(array $data): UnconfirmedTransactionDto
    {
        try {
            return new UnconfirmedTransactionDto(
                id: $data['id'],
                txHash: $data['tx_hash'],
                toAddress: $data['to_address'],
                bcUniqKey: $data['bc_uniq_key'],
                createdAt: new DateTimeImmutable($data['created_at']),
                currencyId: $data['currency_id'],
                blockchain: $data['blockchain'],
                amount: $data['amount'],
                amountUsd: $data['amount_usd'] ?? null,
                networkCreatedAt: new DateTimeImmutable($data['network_created_at']),
                type: $data['type'],
            );
        } catch (Throwable $exception) {
            throw new DvNetInvalidResponseDataException(message: 'Invalid data', previous: $exception);
        }
    }
}
