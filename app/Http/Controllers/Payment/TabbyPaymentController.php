<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\OrderController;
use App\Services\TabbyService;
use App\Utility\NotificationUtility;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Guest;
use Illuminate\Support\Facades\Log;

class TabbyPaymentController extends Controller
{
    protected $tabbyService;

    public function __construct(TabbyService $tabbyService)
    {
        $this->tabbyService = $tabbyService;
    }

    public function pay(Request $request)
    {
        $user = Auth::user();
        if(Auth::user() == null) {
        $temp_user_id = Session::get('temp_user_id');
        $guest = Guest::where('temp_user_id', $temp_user_id)->first();
        $email = $guest->email ?? 'guest@example.com';
        $phone = $guest->phone ?? '0000000000';
        $name  = $guest->name ?? 'Guest';
        }else{
        $email = $user->email ?? 'guest@example.com';
        $phone = $user->phone ?? Guest::where('email', $email)->value('phone') ?? '0000000000';
        $name  = $user->name ?? 'Guest';
        }

        $amount = 0;
        $combined_order = null;

        if (Session::has('payment_type') && Session::get('payment_type') === 'cart_payment') {
            $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
            $amount = $combined_order->grand_total;
        }

        $now = now()->toIso8601String();

        $data = [
            'payment' => [
                'amount' => $amount,
                'currency' => 'SAR',
                'description' => 'Order #' . $combined_order->id,
                'buyer' => [
                    'email' => $email,
                    'phone' => $phone,
                    'name'  => $name,
                    'dob' => '2019-08-24',
                ],
                'shipping_address' => [
                    'city' => '',
                    'address' => '',
                    'zip' => ''
                ],
                'order' => [
                    'tax_amount' => '0.00',
                    'shipping_amount' => '0.00',
                    'discount_amount' => '0.00',
                    'updated_at' => $now,
                    'reference_id' => $combined_order->id,
                    'items' => [
                        [
                            'title' => 'Order Payment',
                            'description' => 'Payment for order #' . $combined_order->id,
                            'quantity' => 1,
                            'unit_price' => $amount,
                            'discount_amount' => '0.00',
                            'reference_id' => $combined_order->id,
                            'image_url' => 'http://example.com',
                            'product_url' => 'http://example.com',
                            'gender' => 'Male',
                            'category' => '',
                            'color' => '',
                            'product_material' => '',
                            'size_type' => '',
                            'size' => '',
                            'brand' => '',
                            'is_refundable' => true,
                        ]
                    ]
                ],
                'buyer_history' => [
                    'registered_since' => $now,
                    'loyalty_level' => 0,
                    'wishlist_count' => 0,
                    'is_social_networks_connected' => true,
                    'is_phone_number_verified' => true,
                    'is_email_verified' => true
                ],
                'order_history' => [
                    [
                        'purchased_at' => $now,
                        'amount' => "100.00",
                        'payment_method' => 'card',
                        'status' => 'new',
                        'buyer' => [
                            'phone' => '',
                            'email' => $email,
                            'name' => '',
                            'dob' => '2019-08-24',
                        ],
                        'shipping_address' => [
                            'city' => '',
                            'address' => '',
                            'zip' => ''
                        ],
                        'items' => [
                            [
                                'title' => '',
                                'description' => '',
                                'quantity' => 1,
                                'unit_price' => '0.00',
                                'discount_amount' => '0.00',
                                'reference_id' => $combined_order->id,
                                'image_url' => 'http://example.com',
                                'product_url' => 'http://example.com',
                                'ordered' => 0,
                                'captured' => 0,
                                'shipped' => 0,
                                'refunded' => 0,
                                'gender' => 'Male',
                                'category' => '',
                                'color' => '',
                                'product_material' => '',
                                'size_type' => '',
                                'size' => '',
                                'brand' => ''
                            ]
                        ]
                    ]
                ],
                'meta' => [
                    'order_id' => $combined_order->id,
                    'customer' => $user->id ?? 0,
                ]
            ],
            'lang' => 'ar',
            'merchant_code' => config('services.tabby.merchant_code'),
            'merchant_urls' => [
                'success' => route('tabby.payment.callBack'),
                'cancel' => route('tabby.payment.fail'),
                'failure' => route('tabby.payment.callBack'),
                'webhook' => route('tabby.webhook'),
            ]
        ];

        try {
            $response = $this->tabbyService->createCheckoutSession($data);

            if (isset($response['id']) && isset($response['configuration']['available_products']['installments'][0]['web_url'])) {
                Session::put('tabby_payment_id', $response['id']);
                return redirect()->away($response['configuration']['available_products']['installments'][0]['web_url']);
            }

        if (isset($response['rejection_reason'])) {
            $errorMessage = translate('Payment could not be processed by Tabby.');

            if ($response['rejection_reason'] === 'order_amount_too_low') {
                $errorMessage = translate('The purchase amount is below the minimum amount required to use Tabby. Please add more items or use another payment method.');
            } elseif ($response['rejection_reason'] === 'order_amount_too_high') {
                $errorMessage = translate('This purchase is above your current spending limit with Tabby. Please try a smaller cart or use another payment method.');
            }

            flash($errorMessage)->error();
        } else {
            flash(translate('Payment could not be processed. Please try again.'))->error();
        }
    } catch (\Exception $e) {
            \Log::error('Tabby payment failed', ['error' => $e->getMessage()]);
            flash(translate('Payment could not be processed.'))->error();
        }

        return redirect()->route('home');
    }

    public function callBack(Request $request)
    {
        try {
            $combined_order_id = Session::get('combined_order_id') ??
                            ($request->merchantTxnId ?? $request->requested_order_id);

            if (Auth::user() == null) {
                (new OrderController)->guest_store($request, true);
                $temp_user_id = Session::get('temp_user_id');

                $combined_order = CombinedOrder::findOrFail($combined_order_id);
                $is_pickup_point = false;

            foreach ($combined_order->orders as $order) {
                $order = Order::find($order->id);

                if ($order && $order->seller_id) {
                    $order->payment_status = 'pending';
                    $order->payment_details = json_encode($request->all());
                    $order->payment_type = 'Tabby';
                    $order->save();

                    if ($order->pickup_point_id != null) {
                        $is_pickup_point = true;
                    }

                    Cart::where('owner_id', $order->seller_id)
                        ->where('temp_user_id', $temp_user_id)
                        ->delete();
                } else {
                    Log::error("Order not found or seller_id is null for order_id: " . $order->id);
                }
            }

                $order = $combined_order->orders->first();
                NotificationUtility::sendNotification($order, 'pending');

                return view('frontend.order_confirmed_guest', compact('order'));
            } else {
                $request->request->add(['requested_order_id' => $combined_order_id]);
                (new OrderController)->store($request, true);

                $combined_order = CombinedOrder::findOrFail($combined_order_id);
                $is_pickup_point = false;

                foreach ($combined_order->orders as $order) {
                    $order = Order::findOrFail($order->id);
                    $order->payment_status = 'pending';
                    $order->payment_details = json_encode($request->all());
                    $order->payment_type = 'Tabby';
                    $order->save();

                    if ($order->pickup_point_id != null) {
                        $is_pickup_point = true;
                    }
                }

                Cart::where('owner_id', $order->seller_id)
                    ->where('user_id', Auth::user()->id)
                    ->delete();

                $order = $combined_order->orders->first();
                NotificationUtility::sendNotification($order, 'pending');

                $checkoutController = new CheckoutController;
                return $checkoutController->checkout_done($combined_order_id, json_encode($request->all()));
            }
        } catch (\Exception $e) {
            Log::error('Error creating pending order: ' . $e->getMessage());
            flash(translate('Error creating order. Please try again.'))->error();
            return redirect()->route('cart');
        }
    }

    public function success(Request $request) {
        if(Auth::user() == null) {
            (new OrderController)->guest_store($request, true);
            $temp_user_id = Session::get('temp_user_id');
            $guest = Guest::where('temp_user_id', $temp_user_id)->first();

            $combined_order = CombinedOrder::findOrFail(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id);
            $is_pickup_point = false;
            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'pending';
                $order->payment_details = json_encode($request->all());
                $order->save();

                if($order->pickup_point_id != null){
                    $is_pickup_point = true;
                }

                Cart::where('owner_id', $order->seller_id)
                ->where('temp_user_id', $temp_user_id)
                ->delete();
            }
            NotificationUtility::sendNotification($order, $request->status);
            return view('frontend.order_confirmed_guest', compact('order'));
        }else{
            $request->request->add(['requested_order_id' => Session::get('combined_order_id')]);
            (new OrderController)->store($request, true);
            $order = Order::where('user_id', Auth::user()->id)->first();

            $combined_order = CombinedOrder::findOrFail(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id);
            $is_pickup_point = false;


            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'pending';
                $order->payment_details = json_encode($request->all());
                $order->save();

                if($order->pickup_point_id != null){
                    $is_pickup_point = true;
                }
            }
            Cart::where('owner_id', $order->seller_id)
                ->where('user_id', Auth::user()->id)
                ->delete();
            NotificationUtility::sendNotification($order, $request->status);
            $checkoutController = new CheckoutController;
            return $checkoutController->checkout_done(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id, json_encode($request->all()));
        }
    }

    public function fail(Request $request)
    {
        flash(translate('Payment failed or was canceled. Please try again.'))->error();
    	return redirect()->route('cart');
    }

    public function webhook(Request $request)
    {
        Log::info('Tabby Webhook received:', $request->all());

        $data = $request->all();

        $orderReference = $data['order']['reference_id'] ?? $data['meta']['order_id'] ?? null;
        $status = strtolower($data['status'] ?? null);

        if (!$orderReference || !$status) {
            Log::warning('Webhook missing required fields');
            return response()->json(['error' => 'Invalid webhook data'], 400);
        }

            $order = Order::where('combined_order_id', $orderReference)->first();
            if ($order) {
                $order->payment_details = json_encode($data);

                switch ($status) {
                    case 'authorized':
                        $order->payment_status = 'paid';
                        break;

                    case 'closed':
                        if (isset($data['captures']) && count($data['captures']) > 0) {
                            $order->payment_status = 'paid';
                        } elseif (isset($data['refunds']) && count($data['refunds']) > 0) {
                            $order->payment_status = 'refunded';
                        }
                        break;

                    case 'rejected':
                        $order->payment_status = 'unpaid';
                        $this->handleOrderCancellation($order);
                        break;

                    case 'expired':
                        $order->payment_status = 'unpaid';
                        $this->handleOrderCancellation($order);
                        break;

                    case 'expired':
                        $order->payment_status = 'unpaid';
                        $order->expired_at = now();
                        $order->is_expired = true;
                        $this->handleOrderCancellation($order);
                        break;

                    default:
                        $order->payment_status = 'pending';
                        break;
                }

                $order->save();
                Log::info("Order {$order->id} updated with status: {$status}");
            }else {
                Log::warning("Order with reference ID {$order->id} not found.");
            }

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }

    private function handleOrderCancellation(Order $order)
    {
        foreach ($order->orderDetails as $orderDetail) {
            $orderDetail->delivery_status = 'cancelled';
            $orderDetail->save();
            product_restock($orderDetail);
        }
    }

}
