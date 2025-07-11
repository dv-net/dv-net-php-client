<?php

declare(strict_types = 1);

namespace Unit;

use DvNet\DvNetClient\Dto\WebhookMapper\ConfirmedWebhookResponse;
use DvNet\DvNetClient\Dto\WebhookMapper\UnconfirmedWebhookResponse;
use DvNet\DvNetClient\WebhookMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    private readonly WebhookMapper $webhook;

    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_USER_ERROR | E_ERROR);
        $this->webhook = new WebhookMapper();
    }

    public static function webhookDataProvider(): array
    {
        return [
            'confirmed transaction' => [
                'class' => ConfirmedWebhookResponse::class,
                'data' => json_decode(file_get_contents(__DIR__ . '/../Assets/confirmed.json'), true),
            ],
            'unconfirmed transaction' => [
                'class' => UnconfirmedWebhookResponse::class,
                'data' => json_decode(file_get_contents(__DIR__ . '/../Assets/unconfirmed.json'), true),
            ],
        ];
    }

    public static function invalidWebhookDataProvider(): array
    {
        return [
            'invalid or empty field' => [
                'message' => '/cannot map webhook/',
                'data' => json_decode(file_get_contents(__DIR__ . '/../Assets/wrong.json'), true),
            ],
            'empty json' => [
                'message' => '/invalid webhook format/',
                'data' => json_decode(file_get_contents(__DIR__ . '/../Assets/empty.json'), true),
            ],
        ];
    }

    #[DataProvider('webhookDataProvider')]
    public function testCorrectWebhookMapping(string $class, array $data): void
    {
        $result = $this->webhook->mapWebhook($data);
        $this->assertSame($class, get_class($result));
        $this->assertSame($result->type, $data['type'] ?? $data['unconfirmed_type']);
        $this->assertSame($result->transactions->txId, $data['transactions']['tx_id'] ?? $data['unconfirmed_transaction']['tx_id'] ?? $data['unconfirmed_transactions']['unconfirmed_tx_id']);
        $this->assertSame($result->wallet->id, $data['wallet']['id'] ?? $data['unconfirmed_wallet']['unconfirmed_id']);
    }

    #[DataProvider('invalidWebhookDataProvider')]
    public function testExceptions(string $message, array $data): void
    {
        $this->expectExceptionMessageMatches($message);
        $this->webhook->mapWebhook($data);
    }
}
