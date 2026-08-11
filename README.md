# geekmac/laravel-payhere

A small, dependency-light PayHere payment gateway integration for Laravel.
Built to replace `lahirulhr/laravel-payhere`, which is unmaintained and
capped at Laravel 11 — this package supports Laravel 11, 12, and 13.

Implements the [PayHere Checkout API](https://support.payhere.lk/api-&-mobile-sdk/checkout-api)
hash generation and payment notification verification, matching PayHere's
documented algorithm exactly.

## Requirements

- PHP 8.2+
- Laravel 11.x, 12.x, or 13.x

## Installation

### Option A — private VCS repository (recommended if not publishing to Packagist)

Push this package to its own git repository (e.g. `github.com/geekmac/laravel-payhere`),
then in your app's `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/geekmac/laravel-payhere"
    }
],
"require": {
    "geekmac/laravel-payhere": "^1.0"
}
```

Tag a release (`git tag v1.0.0 && git push --tags`) so Composer has a version
to resolve, then:

```bash
composer require geekmac/laravel-payhere
```

### Option B — path repository (fastest for local development)

If you're keeping the package in the same monorepo or a sibling folder:

```json
"repositories": [
    {
        "type": "path",
        "url": "../laravel-payhere"
    }
],
"require": {
    "geekmac/laravel-payhere": "*"
}
```

```bash
composer require geekmac/laravel-payhere:@dev
```

### Option C — publish to Packagist

Once you're happy with it, submit the repo at [packagist.org/packages/submit](https://packagist.org/packages/submit)
and it becomes a normal `composer require geekmac/laravel-payhere`.

Laravel's package auto-discovery registers the service provider and
`PayHere` facade automatically — no manual registration needed.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=payhere-config
```

Set your credentials in `.env` (from PayHere dashboard → Integrations):

```
PAYHERE_SANDBOX=true
PAYHERE_MERCHANT_ID=your_merchant_id
PAYHERE_MERCHANT_SECRET=your_merchant_secret
PAYHERE_CURRENCY=LKR
```

## Usage

### Quick start (built-in routes)

By default the package registers ready-to-use routes:

| Route | Name | Purpose |
|---|---|---|
| `GET /payment/checkout` | `payhere.checkout` | Builds and auto-submits the checkout form |
| `GET /payment/success` | `payhere.success` | Return URL after successful payment |
| `GET /payment/cancel` | `payhere.cancel` | Return URL after cancelled payment |
| `POST /payment/notify` | `payhere.notify` | Server-to-server webhook (no CSRF) |

Link to `route('payhere.checkout', ['order_id' => ..., 'amount' => ..., ...])`
to start a payment, or set `PAYHERE_REGISTER_ROUTES=false` and build your own
controller using the facade directly (see below).

### Using the facade directly

```php
use PayHere; // or Geekmac\PayHere\Facades\PayHere;

$checkoutFields = PayHere::buildCheckout([
    'order_id'   => $order->id,
    'items'      => 'Premium Plan',
    'amount'     => 2500.00,
    'first_name' => $user->first_name,
    'last_name'  => $user->last_name,
    'email'      => $user->email,
    'phone'      => $user->phone,
    'address'    => $user->address,
    'city'       => $user->city,
    'country'    => 'Sri Lanka',
]);

return view('payhere::checkout', [
    'checkoutUrl'    => PayHere::checkoutUrl(),
    'checkoutFields' => $checkoutFields,
]);
```

### Verifying the notify webhook

```php
use Geekmac\PayHere\PayHereService;

public function notify(Request $request, PayHereService $payHere)
{
    $params = $request->all();

    if (!$payHere->verifyNotification($params)) {
        return response('Invalid signature', 400);
    }

    if ($payHere->isSuccessful($params)) {
        // mark order as paid
    }

    return response('OK', 200);
}
```

## Important: CSRF exemption for the notify webhook

PayHere calls `notify_url` server-to-server with no CSRF token. The
package's built-in route is registered outside the `web` middleware group,
so it's exempt automatically. If you build your own route for this instead,
make sure to exclude it from CSRF verification — see your Laravel version's
CSRF middleware config (`bootstrap/app.php` for Laravel 11/12; Laravel 13
renamed the middleware to `PreventRequestForgery`).

## Testing

```bash
composer install
vendor/bin/phpunit
```

Tests verify the hash algorithm and notification-signature verification
against manually computed values, so you can confirm correctness without
hitting PayHere's servers.

## What's covered

- Checkout API (payment initiation + hash)
- Payment notification verification (webhook signature)

## What's not covered (yet)

Recurring, Preapproval, Charging, Retrieval, Subscription Manager, Refund,
Authorize, and Capture APIs — the original package wrapped these too. They
follow the same hash pattern as Checkout, so they're straightforward to add
to `PayHereService` if you need them.

## License

MIT
