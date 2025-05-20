<?php

declare(strict_types=1);

namespace DvNet\DvNetClient;

use DateTimeImmutable;
use DvNet\DvNetClient\Dto\WebhookMapper\TransactionDto;
use DvNet\DvNetClient\Dto\WebhookMapper\WalletDto;
use DvNet\DvNetClient\Dto\WebhookMapper\ConfirmedWebhookResponse;
use DvNet\DvNetClient\Dto\WebhookMapper\UnconfirmedWebhookResponse;
use DvNet\DvNetClient\Dto\WebhookMapper\WithdrawalWebhookResponse;
use DvNet\DvNetClient\Exceptions\DvNetInvalidWebhookException;

class WebhookMapper
{
    /**
     * @param array<string, mixed> $rawWebhookData
     * @throws DvNetInvalidWebhookException
     */
    public function mapWebhook(array $rawWebhookData): ConfirmedWebhookResponse|UnconfirmedWebhookResponse|WithdrawalWebhookResponse
    {
        try {
            if (isset($rawWebhookData['withdrawal_id'])) {
                return new WithdrawalWebhookResponse(
                    $rawWebhookData['type'],
                    $rawWebhookData['status'],
                    new DateTimeImmutable($rawWebhookData['created_at']),
                    new DateTimeImmutable($rawWebhookData['paid_at']),
                    $rawWebhookData['amount'],
                    new TransactionDto(
                        $rawWebhookData['transactions']['tx_id'],
                        $rawWebhookData['transactions']['tx_hash'],
                        $rawWebhookData['transactions']['bc_uniq_key'],
                        new DateTimeImmutable($rawWebhookData['transactions']['created_at']),
                        $rawWebhookData['transactions']['currency'],
                        $rawWebhookData['transactions']['currency_id'],
                        $rawWebhookData['transactions']['blockchain'],
                        $rawWebhookData['transactions']['amount'],
                        $rawWebhookData['transactions']['amount_usd'],
                    ),
                    new WalletDto(
                        $rawWebhookData['wallet']['id'],
                        $rawWebhookData['wallet']['store_external_id'],
                    ),
                    $rawWebhookData['withdrawal_id'],
                );
            }

            if (isset($rawWebhookData['type'])) {
                return new ConfirmedWebhookResponse(
                    $rawWebhookData['type'],
                    $rawWebhookData['status'],
                    new DateTimeImmutable($rawWebhookData['created_at']),
                    new DateTimeImmutable($rawWebhookData['paid_at']),
                    $rawWebhookData['amount'],
                    new TransactionDto(
                        $rawWebhookData['transactions']['tx_id'],
                        $rawWebhookData['transactions']['tx_hash'],
                        $rawWebhookData['transactions']['bc_uniq_key'],
                        new DateTimeImmutable($rawWebhookData['transactions']['created_at']),
                        $rawWebhookData['transactions']['currency'],
                        $rawWebhookData['transactions']['currency_id'],
                        $rawWebhookData['transactions']['blockchain'],
                        $rawWebhookData['transactions']['amount'],
                        $rawWebhookData['transactions']['amount_usd'],
                    ),
                    new WalletDto(
                        $rawWebhookData['wallet']['id'],
                        $rawWebhookData['wallet']['store_external_id'],
                    ),
                );
            }

            if (isset($rawWebhookData['unconfirmed_type'])) {
                return new UnconfirmedWebhookResponse(
                    $rawWebhookData['unconfirmed_type'],
                    $rawWebhookData['unconfirmed_status'],
                    new DateTimeImmutable($rawWebhookData['unconfirmed_created_at']),
                    new DateTimeImmutable($rawWebhookData['unconfirmed_paid_at']),
                    $rawWebhookData['unconfirmed_amount'],
                    new TransactionDto(
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_tx_id'],
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_tx_hash'],
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_bc_uniq_key'],
                        new DateTimeImmutable($rawWebhookData['unconfirmed_transactions']['unconfirmed_created_at']),
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_currency'],
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_currency_id'],
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_blockchain'],
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_amount'],
                        $rawWebhookData['unconfirmed_transactions']['unconfirmed_amount_usd'],
                    ),
                    new WalletDto(
                        $rawWebhookData['unconfirmed_wallet']['unconfirmed_id'],
                        $rawWebhookData['unconfirmed_wallet']['unconfirmed_store_external_id'],
                    ),
                );
            }
        } catch (\Throwable $th) {
            throw new DvNetInvalidWebhookException('cannot map webhook: ' . $th->getMessage(), 1, $th);
        }

        throw new DvNetInvalidWebhookException('invalid webhook format, missing "type", "withdrawal_id" or "unconfirmed_type" field');
    }
}
