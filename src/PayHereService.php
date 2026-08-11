<?php

namespace Geekmac\PayHere;

use InvalidArgumentException;

class PayHereService
{
    protected string $merchantId;
    protected string $merchantSecret;
    protected string $currency;
    protected bool $sandbox;

    public function __construct()
    {
        $this->merchantId = (string) config('payhere.merchant_id');
        $this->merchantSecret = (string) config('payhere.merchant_secret');
        $this->currency = (string) config('payhere.currency', 'LKR');
        $this->sandbox = (bool) config('payhere.sandbox', true);

        if ($this->merchantId === '' || $this->merchantSecret === '') {
            throw new InvalidArgumentException(
                'PayHere merchant_id and merchant_secret must be set (publish config/payhere.php and set PAYHERE_MERCHANT_ID, PAYHERE_MERCHANT_SECRET in .env).'
            );
        }
    }

    /**
     * The checkout endpoint to POST the form to (sandbox or live).
     */
    public function checkoutUrl(): string
    {
        return $this->sandbox
            ? config('payhere.checkout_url.sandbox')
            : config('payhere.checkout_url.live');
    }

    /**
     * Build the full set of fields required for the PayHere Checkout API,
     * including the server-generated hash. Pass the result straight into
     * a form (see the published payhere::checkout view).
     *
     * Required $data keys: order_id, items, amount, first_name, last_name,
     * email, phone, address, city, country.
     * Optional: currency (defaults to config), plus any of PayHere's
     * optional fields (delivery_address, custom_1, custom_2, item_number_1, etc).
     */
    public function buildCheckout(array $data): array
    {
        foreach (['order_id', 'items', 'amount', 'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'country'] as $required) {
            if (!array_key_exists($required, $data) || $data[$required] === null || $data[$required] === '') {
                throw new InvalidArgumentException("PayHere checkout data is missing required field: {$required}");
            }
        }

        $currency = $data['currency'] ?? $this->currency;
        $amount = $this->formatAmount($data['amount']);

        return array_merge($data, [
            'merchant_id' => $this->merchantId,
            'currency'    => $currency,
            'amount'      => $amount,
            'return_url'  => $this->resolveUrl(config('payhere.return_url')),
            'cancel_url'  => $this->resolveUrl(config('payhere.cancel_url')),
            'notify_url'  => $this->resolveUrl(config('payhere.notify_url')),
            'hash'        => $this->generateHash($data['order_id'], $amount, $currency),
        ]);
    }

    /**
     * hash = UPPER( MD5( merchant_id + order_id + amount + currency + UPPER(MD5(merchant_secret)) ) )
     * Per PayHere's Checkout API docs. Never expose merchant_secret client-side —
     * this must run server-side only.
     */
    public function generateHash(string $orderId, string $amount, ?string $currency = null): string
    {
        $currency ??= $this->currency;

        return strtoupper(md5(
            $this->merchantId
            . $orderId
            . $amount
            . $currency
            . strtoupper(md5($this->merchantSecret))
        ));
    }

    /**
     * Verify an inbound payment notification (webhook) hit at notify_url.
     * Call this before trusting a notification's status_code.
     *
     * md5sig = UPPER( MD5( merchant_id + order_id + payhere_amount + payhere_currency + status_code + UPPER(MD5(merchant_secret)) ) )
     */
    public function verifyNotification(array $params): bool
    {
        foreach (['order_id', 'payhere_amount', 'payhere_currency', 'status_code', 'md5sig'] as $required) {
            if (!array_key_exists($required, $params)) {
                return false;
            }
        }

        $expected = strtoupper(md5(
            $this->merchantId
            . $params['order_id']
            . $params['payhere_amount']
            . $params['payhere_currency']
            . $params['status_code']
            . strtoupper(md5($this->merchantSecret))
        ));

        return hash_equals($expected, strtoupper((string) $params['md5sig']));
    }

    /**
     * PayHere status_code meanings, for readability at call sites.
     * 2 = success, 0 = pending, -1 = cancelled, -2 = failed, -3 = chargedback
     */
    public function isSuccessful(array $notificationParams): bool
    {
        return (int) ($notificationParams['status_code'] ?? null) === 2;
    }

    protected function formatAmount(int|float|string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    protected function resolveUrl(string $urlOrRouteName): string
    {
        if (str_starts_with($urlOrRouteName, 'http://') || str_starts_with($urlOrRouteName, 'https://')) {
            return $urlOrRouteName;
        }

        if (str_starts_with($urlOrRouteName, '/')) {
            return url($urlOrRouteName);
        }

        return route($urlOrRouteName);
    }
}
