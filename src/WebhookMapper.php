<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient;

use DateTimeImmutable;
use DvNet\DvNetClient\Dto\WebhookMapper\ConfirmedWebhookResponse;
use DvNet\DvNetClient\Dto\WebhookMapper\TransactionDto;
use DvNet\DvNetClient\Dto\WebhookMapper\UnconfirmedWebhookResponse;
use DvNet\DvNetClient\Dto\WebhookMapper\WalletDto;
use DvNet\DvNetClient\Dto\WebhookMapper\WithdrawalWebhookResponse;
use DvNet\DvNetClient\Exceptions\DvNetInvalidWebhookException;
use Throwable;

/**
 * @psalm-type ConfirmedWebhook = array{
 *      type: string,
 *      status: string,
 *      created_at: string,
 *      paid_at: string,
 *      amount: string,
 *      transactions: Transaction,
 *      wallet: Wallet,
 * }
 * @psalm-type UnconfirmedWebhook = array{
 *     unconfirmed_type: string,
 *     unconfirmed_status: string,
 *     unconfirmed_created_at: string,
 *     unconfirmed_paid_at: string,
 *     unconfirmed_amount: string,
 *     unconfirmed_transactions: UnconfirmedTransaction,
 *     unconfirmed_wallet: UnconfirmedWallet,
 * }
 * @psalm-type WithdrawalWebhook = array{
 *      withdrawal_id: string,
 *      type: string,
 *      status: string,
 *      created_at: string,
 *      paid_at: string,
 *      amount: string,
 *      transactions: Transaction,
 *      wallet: Wallet,
 *  }
 * @psalm-type Transaction = array{
 *      tx_id: string,
 *      tx_hash: string,
 *      bc_uniq_key: string,
 *      created_at: string,
 *      currency: string,
 *      currency_id: string,
 *      blockchain: string,
 *      amount: string,
 *      amount_usd: string
 *  }
 * @psalm-type Wallet = array{
 *      id: string,
 *      store_external_id: string,
 *  }
 * @psalm-type UnconfirmedTransaction = array{
 *      unconfirmed_tx_id: string,
 *      unconfirmed_tx_hash: string,
 *      unconfirmed_bc_uniq_key: string,
 *      unconfirmed_created_at: string,
 *      unconfirmed_currency: string,
 *      unconfirmed_currency_id: string,
 *      unconfirmed_blockchain: string,
 *      unconfirmed_amount: string,
 *      unconfirmed_amount_usd: string
 * }
 * @psalm-type UnconfirmedWallet = array{
 *      unconfirmed_id: string,
 *      unconfirmed_store_external_id: string
 * }
 */
class WebhookMapper
{
    /**
     * @param ConfirmedWebhook|UnconfirmedWebhook|WithdrawalWebhook $rawWebhookData
     *
     * @throws DvNetInvalidWebhookException
     */
    public function mapWebhook(array $rawWebhookData): ConfirmedWebhookResponse|UnconfirmedWebhookResponse|WithdrawalWebhookResponse
    {
        try {
            if (isset($rawWebhookData['withdrawal_id'])) {
                /* @var WithdrawalWebhook $rawWebhookData */
                return $this->makeWithdrawalWebhook($rawWebhookData);
            }

            if (isset($rawWebhookData['type'])) {
                /* @var ConfirmedWebhook $rawWebhookData */
                return $this->makeConfirmedWebhook($rawWebhookData);
            }

            if (isset($rawWebhookData['unconfirmed_type'])) {
                /* @var UnconfirmedWebhook $rawWebhookData */
                return $this->makeUnconfirmedWebhook($rawWebhookData);
            }
        } catch (Throwable $th) {
            throw new DvNetInvalidWebhookException('cannot map webhook: ' . $th->getMessage(), 1, $th);
        }

        throw new DvNetInvalidWebhookException('invalid webhook format, missing "type", "withdrawal_id" or "unconfirmed_type" field');
    }

    /**
     * @param Wallet|UnconfirmedWallet $data
     */
    private function makeWallet(array $data, string $prefix = ''): WalletDto
    {
        return new WalletDto(
            id: $data[$prefix . 'id'],
            storeExternalId: $data[$prefix . 'store_external_id'],
        );
    }

    /**
     * @param Transaction|UnconfirmedTransaction $data
     */
    private function makeTransaction(array $data, string $prefix = ''): TransactionDto
    {
        return new TransactionDto(
            txId: $data[$prefix . 'tx_id'],
            txHash: $data[$prefix . 'tx_hash'],
            bcUniqKey: $data[$prefix . 'bc_uniq_key'],
            createdAt: new DateTimeImmutable($data[$prefix . 'created_at']),
            currency: $data[$prefix . 'currency'],
            currencyId: $data[$prefix . 'currency_id'],
            blockchain: $data[$prefix . 'blockchain'],
            amount: $data[$prefix . 'amount'],
            amountUsd: $data[$prefix . 'amount_usd'],
        );
    }

    /**
     * @param WithdrawalWebhook $data
     */
    private function makeWithdrawalWebhook(array $data): WithdrawalWebhookResponse
    {
        return new WithdrawalWebhookResponse(
            type: $data['type'],
            createdAt: new DateTimeImmutable($data['created_at']),
            paidAt: new DateTimeImmutable($data['paid_at']),
            amount: $data['amount'],
            transactions: $this->makeTransaction($data['transactions']),
            withdrawalId: $data['withdrawal_id'],
        );
    }

    /**
     * @param ConfirmedWebhook $data
     */
    private function makeConfirmedWebhook(array $data): ConfirmedWebhookResponse
    {
        return new ConfirmedWebhookResponse(
            type: $data['type'],
            status: $data['status'],
            createdAt: new DateTimeImmutable($data['created_at']),
            paidAt: new DateTimeImmutable($data['paid_at']),
            amount: $data['amount'],
            transactions: $this->makeTransaction($data['transactions']),
            wallet: $this->makeWallet($data['wallet']),
        );
    }

    /**
     * @param UnconfirmedWebhook $data
     */
    private function makeUnconfirmedWebhook(array $data): UnconfirmedWebhookResponse
    {
        return new UnconfirmedWebhookResponse(
            type: $data['unconfirmed_type'],
            status: $data['unconfirmed_status'],
            createdAt: new DateTimeImmutable($data['unconfirmed_created_at']),
            paidAt: new DateTimeImmutable($data['unconfirmed_paid_at']),
            amount: $data['unconfirmed_amount'],
            transactions: $this->makeTransaction($data['unconfirmed_transactions'], 'unconfirmed_'),
            wallet: $this->makeWallet($data['unconfirmed_wallet'], 'unconfirmed_'),
        );
    }
}
