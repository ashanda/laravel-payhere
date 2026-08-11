<?php

namespace Geekmac\PayHere\Facades;

use Geekmac\PayHere\PayHereService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string checkoutUrl()
 * @method static array buildCheckout(array $data)
 * @method static string generateHash(string $orderId, string $amount, ?string $currency = null)
 * @method static bool verifyNotification(array $params)
 * @method static bool isSuccessful(array $notificationParams)
 *
 * @see \Geekmac\PayHere\PayHereService
 */
class PayHere extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PayHereService::class;
    }
}
