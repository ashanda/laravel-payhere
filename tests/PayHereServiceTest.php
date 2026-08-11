<?php

namespace Geekmac\PayHere\Tests;

use Geekmac\PayHere\PayHereService;

class PayHereServiceTest extends TestCase
{
    public function test_generate_hash_matches_payhere_algorithm(): void
    {
        $service = app(PayHereService::class);

        // Manually compute the expected hash using PayHere's documented
        // algorithm to confirm the service matches it exactly:
        // hash = UPPER(MD5(merchant_id + order_id + amount + currency + UPPER(MD5(merchant_secret))))
        $merchantId = config('payhere.merchant_id');
        $merchantSecret = config('payhere.merchant_secret');
        $orderId = 'ItemNo12345';
        $amount = '1000.00';
        $currency = 'LKR';

        $expected = strtoupper(md5(
            $merchantId . $orderId . $amount . $currency . strtoupper(md5($merchantSecret))
        ));

        $this->assertSame($expected, $service->generateHash($orderId, $amount, $currency));
    }

    public function test_build_checkout_includes_correct_hash_and_urls(): void
    {
        $service = app(PayHereService::class);

        $fields = $service->buildCheckout([
            'order_id'   => 'ItemNo12345',
            'items'      => 'Door bell wireless',
            'amount'     => 1000,
            'first_name' => 'Saman',
            'last_name'  => 'Perera',
            'email'      => 'samanp@gmail.com',
            'phone'      => '0771234567',
            'address'    => 'No.1, Galle Road',
            'city'       => 'Colombo',
            'country'    => 'Sri Lanka',
        ]);

        $this->assertSame('1211149', $fields['merchant_id']);
        $this->assertSame('1000.00', $fields['amount']);
        $this->assertSame('LKR', $fields['currency']);
        $this->assertNotEmpty($fields['hash']);
        $this->assertSame(
            $service->generateHash('ItemNo12345', '1000.00', 'LKR'),
            $fields['hash']
        );
    }

    public function test_build_checkout_throws_on_missing_required_field(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(PayHereService::class)->buildCheckout([
            'order_id' => 'ItemNo12345',
            // 'items' intentionally omitted
            'amount' => 1000,
        ]);
    }

    public function test_verify_notification_accepts_valid_signature(): void
    {
        $service = app(PayHereService::class);
        $merchantId = config('payhere.merchant_id');
        $merchantSecret = config('payhere.merchant_secret');

        $params = [
            'order_id'         => 'ItemNo12345',
            'payhere_amount'   => '1000.00',
            'payhere_currency' => 'LKR',
            'status_code'      => '2',
        ];

        $params['md5sig'] = strtoupper(md5(
            $merchantId
            . $params['order_id']
            . $params['payhere_amount']
            . $params['payhere_currency']
            . $params['status_code']
            . strtoupper(md5($merchantSecret))
        ));

        $this->assertTrue($service->verifyNotification($params));
        $this->assertTrue($service->isSuccessful($params));
    }

    public function test_verify_notification_rejects_tampered_signature(): void
    {
        $service = app(PayHereService::class);

        $params = [
            'order_id'         => 'ItemNo12345',
            'payhere_amount'   => '1000.00',
            'payhere_currency' => 'LKR',
            'status_code'      => '2',
            'md5sig'           => 'NOT-THE-REAL-SIGNATURE',
        ];

        $this->assertFalse($service->verifyNotification($params));
    }

    public function test_verify_notification_rejects_missing_fields(): void
    {
        $service = app(PayHereService::class);

        $this->assertFalse($service->verifyNotification([
            'order_id' => 'ItemNo12345',
            // missing everything else
        ]));
    }
}
