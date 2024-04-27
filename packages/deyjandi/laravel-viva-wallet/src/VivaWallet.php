<?php

namespace Deyjandi\VivaWallet;

class VivaWallet
{
    private array $config;

    public function __construct()
    {
        $this->config = config('viva-wallet');
    }

    public function requestWebhookKey(): string
    {
        return (new VivaWalletWebhook($this->config))->requestKey();
    }

    public static function createPaymentOrder(Payment $payment, ?Customer $customer = null): string
    {
        $config = config('viva-wallet');

        return $payment->getCheckoutUrl(
            $payment
                ->setConfig($config)
                ->setCustomer($customer)
                ->createOrder()
        );
    }

    public static function retrieveTransaction(string $transactionId): array
    {
        $config = config('viva-wallet');
        return (new VivaWalletTransaction($config))->retrieve($transactionId);
    }
}
