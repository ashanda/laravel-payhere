<?php

namespace Geekmac\PayHere\Http\Controllers;

use Geekmac\PayHere\Facades\PayHere;
use Geekmac\PayHere\PayHereService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Default example controller, registered automatically when
 * config('payhere.register_routes') is true.
 *
 * This is a starting point, not a full checkout flow — override it in your
 * own app by publishing routes (php artisan vendor:publish --tag=payhere-views)
 * and setting PAYHERE_REGISTER_ROUTES=false, or by extending this class.
 */
class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $checkoutFields = PayHere::buildCheckout([
            'order_id'   => (string) $request->query('order_id', uniqid('order_')),
            'items'      => $request->query('items', 'Order'),
            'amount'     => (float) $request->query('amount', 0),
            'first_name' => $request->user()?->first_name ?? $request->query('first_name', 'Guest'),
            'last_name'  => $request->user()?->last_name ?? $request->query('last_name', ''),
            'email'      => $request->user()?->email ?? $request->query('email', 'guest@example.com'),
            'phone'      => $request->query('phone', ''),
            'address'    => $request->query('address', ''),
            'city'       => $request->query('city', ''),
            'country'    => $request->query('country', 'Sri Lanka'),
        ]);

        return view('payhere::checkout', [
            'checkoutUrl'    => PayHere::checkoutUrl(),
            'checkoutFields' => $checkoutFields,
        ]);
    }

    public function success(Request $request)
    {
        return view('payhere::success');
    }

    public function cancel(Request $request)
    {
        return view('payhere::cancel');
    }

    /**
     * Server-to-server webhook PayHere calls after a payment attempt.
     * Registered outside the 'web' middleware group, so it is not subject
     * to CSRF verification (PayHere sends no token).
     */
    public function notify(Request $request, PayHereService $payHere)
    {
        $params = $request->all();

        if (!$payHere->verifyNotification($params)) {
            report(new \RuntimeException('PayHere: invalid notification signature for order ' . ($params['order_id'] ?? 'unknown')));
            return response('Invalid signature', 400);
        }

        if ($payHere->isSuccessful($params)) {
            // TODO: mark the matching order as paid, e.g.:
            // Order::where('id', $params['order_id'])->update(['status' => 'paid']);
        } else {
            // status_code: 0 pending, -1 cancelled, -2 failed, -3 chargedback
            // TODO: handle non-success states as needed.
        }

        return response('OK', 200);
    }
}
