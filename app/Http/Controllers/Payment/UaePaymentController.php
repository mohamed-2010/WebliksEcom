<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Guest;
use Illuminate\Support\Facades\Session;
use App\Utility\NotificationUtility;
use App\Http\Controllers\OrderController;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UaePaymentController extends Controller
{
    public function pay()
    {
        $user = Auth::user();
        $email = $user && $user->email ? $user->email : 'customer@example.com';
        $phone = $user && $user->phone ? $user->phone : '0000000000';

        $amount = 0;
        $combined_order = null;

        if (Session::has('payment_type') && Session::get('payment_type') == 'cart_payment') {
            $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

            $carts = $user
                ? Cart::where('user_id', $user->id)->get()
                : Cart::where('temp_user_id', Session::get('temp_user_id'))->get();

            $amount = calculateCartSubTotal($carts);
            $amount = round($amount, 2);
        }

        $payment_data = [
            'order_id' => $combined_order->id,
            'amount' => $amount,
            'currency' => 'AED',
            'customer_email' => $email,
            'customer_phone' => $phone,
            'success_url' => route('uae.payment.redirect'),
            'fail_url' => route('uae.payment.redirect'),
            'webhook_url' => route('uae.payment.webhook'),
        ];

        $payment_gateway_url = $this->initiateUaeGatewayPayment($payment_data);

        if ($payment_gateway_url) {
            return redirect()->away($payment_gateway_url);
        }

        flash(translate('Payment initialization failed'))->error();
        return back();
    }

    protected function initiateUaeGatewayPayment($data)
    {
        $url = config('services.uae_gateway.url');
        $apiKey = config('services.uae_gateway.key');
        $apiSecret = config('services.uae_gateway.secret');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ])->post($url, [
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'order_id' => $data['order_id'],
                'success_url' => $data['success_url'],
                'fail_url' => $data['fail_url'],
                'webhook_url' => $data['webhook_url'],
                'secret' => $apiSecret,
            ]);

            if ($response->successful() && isset($response['checkout_url'])) {
                return $response['checkout_url'];
            }

            Log::error('UAE Gateway error', ['response' => $response->json()]);
            return false;

        } catch (\Exception $e) {
            Log::error('UAE Gateway exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function success(Request $request)
    {
        if (Auth::user() == null) {
            $request['payment_option'] = "uae_gateway";
            (new OrderController)->guest_store($request, true);

            $combined_order = CombinedOrder::findOrFail($request->order_id ?? Session::get('combined_order_id'));

            foreach ($combined_order->orders as $order) {
                $order->payment_status = 'pending';
                $order->payment_details = json_encode($request->all());
                $order->save();

                Cart::where('owner_id', $order->seller_id)
                    ->where('temp_user_id', Session::get('temp_user_id'))
                    ->delete();
            }

            NotificationUtility::sendNotification($order, 'pending');

            return view('frontend.order_confirmed_guest', compact('order'));
        } else {
            $request['payment_option'] = "uae_gateway";
            (new OrderController)->store($request, true);

            $combined_order = CombinedOrder::findOrFail($request->order_id ?? Session::get('combined_order_id'));

            foreach ($combined_order->orders as $order) {
                $order->payment_status = 'pending';
                $order->payment_details = json_encode($request->all());
                $order->save();
            }

            NotificationUtility::sendNotification($order, 'pending');

            return view('frontend.order_confirmed', compact('order'));
        }
    }

    public function fail(Request $request)
    {
        flash('Payment failed or was canceled.')->error();
        return redirect()->route('home');
    }
}
