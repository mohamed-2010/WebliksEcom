<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Times;
use App\Utility\PayfastUtility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Address;
use App\Models\Carrier;
use App\Models\CombinedOrder;
use App\Models\Product;
use App\Models\PickupPoint;
use App\Models\Guest;
use App\Utility\PayhereUtility;
use App\Utility\NotificationUtility;
use Illuminate\Support\Str;
use Log;
use Session;
use Auth;
use Freshbitsweb\LaravelGoogleAnalytics4MeasurementProtocol\Facades\GA4;

class CheckoutController extends Controller
{

    public function __construct()
    {
        //
    }

    public function getWalletAndTotal()
    {
        $carts = Cart::where('user_id', Auth::user()->id)->get();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        foreach ($carts as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
            $shipping += $cartItem['shipping_cost'];
        }

        // if total of order > get_setting('free_delivery_after') will set shipping is 0
        if ($subtotal > get_setting('free_delivery_after') && get_setting('free_delivery_after_enabled')) {
            $shipping = 0;
        }

        $total = $subtotal + $tax + $shipping;
        $wallet_balance = Auth::user()->balance;
        $remaining_price = max(0, $total - $wallet_balance);

        return response()->json([
            'wallet_balance' => single_price($wallet_balance),
            'total' => single_price($total),
            'remaining_price' => single_price($remaining_price)
        ]);
    }

    //check the selected payment gateway and redirect to that controller accordingly
    public function checkout(Request $request)
    {
    //    return Carbon::now()->format('H:i');
        // Order Limit
        $order_limit = BusinessSetting::where('type', 'order_limit')->first()->value;
        $orders_day = Order::whereDate('created_at' , '=', Carbon::now())->count();
        if ($order_limit != 0) {
            if ($order_limit <= $orders_day){

                flash(translate(translate('The maximum number of orders has been reached today. You can order later')))->warning();
                return redirect()->route('home');
            }
        }
        //Time Work

        $time = 0;
        $times = Times::first();
        if($times != null) {
            $currentDay = Str::lower(Carbon::now()->format('l'));
            $currentTime = Carbon::now();
            $times = $times->$currentDay;

            $openTimes = [];
            $closeTimes = [];
            $withinWorkingHours = false;

            if($times != null) {
                foreach ($times as $key => $value) {
                    $openTimes[] = Carbon::parse($value['open'])->format('H:i');
                    $closeTimes[] = Carbon::parse($value['close'])->format('H:i');
                    if ($currentTime->between(Carbon::parse($value['open']), Carbon::parse($value['close']))) {
                        $withinWorkingHours = true;
                        break;
                    }
                }

                if (!$withinWorkingHours) {
                    $openTime = min($openTimes);
                    $closeTime = max($closeTimes);
                    flash(translate('Official working hours today from ') . ' ' . $openTime . ' ' . translate('to') . ' ' . $closeTime)->warning();
                    $time = 1;
                }
            }
        }

        // Minumum order amount check
        if (get_setting('minimum_order_amount_check') == 1) {
            $subtotal = 0;
            foreach (Cart::where('user_id', Auth::user()->id)->get() as $key => $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                foreach(json_decode($cartItem['addons']) as $addon) {
                    if($addon->quantity > 0) {
                        $subtotal += $addon->price * $addon->quantity;
                    }
                }
            }
            if ($subtotal < get_setting('minimum_order_amount')) {
                flash(translate('You order amount is less then the minimum order amount'))->warning();
                return redirect()->route('home');
            }
        }

        // Minumum order amount check end

        if ($request->payment_option != null) {

            $carts = Cart::where('user_id', Auth::user()->id)
                ->get();

            if ($carts->isEmpty()) {
                flash(translate('Your cart is empty'))->warning();
                return redirect()->route('home');
            }

            if($carts[0]['address_id'] != null) {
                $address = Address::where('id', $carts[0]['address_id'])->first();

                if ($address->city == null) {
                    flash(translate('Please select your city'))->warning();
                    return redirect()->route('checkout.shipping_info');
                }
            } else {
                if($carts[0]['pickup_point'] == null || $carts[0]['pickup_point'] == 0) {
                    flash(translate('Please select your pickup point'))->warning();
                    return redirect()->route('checkout.shipping_info');
                }
            }

            // Old Way To create Order
            // (new OrderController)->store($request);

            // $request->session()->put('payment_type', 'cart_payment');

            // $data['combined_order_id'] = $request->session()->get('combined_order_id');
            // $request->session()->put('payment_data', $data);
            // end

            //start
            (new OrderController)->store($request, false);

            $request->session()->put('payment_type', 'cart_payment');
            //end

            // if ($request->session()->get('combined_order_id') != null) {

                // If block for Online payment, wallet and cash on delivery. Else block for Offline payment
                $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
                if (class_exists($decorator)) {
                    if(get_setting('google_analytics_enabled')) {
                        GA4::postEvent([
                            'name' => 'add_payment_info',
                            'params' => [
                                'currency' => currency_symbol(),
                                'value'    => $request->amount,
                            ]
                        ]);
                    }
                    $controller = app()->make($decorator);
                    return $controller->pay($request);
                } else {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                    $manual_payment_data = array(
                        'name'   => $request->payment_option,
                        'amount' => $combined_order->grand_total,
                        'trx_id' => $request->trx_id,
                        'photo'  => $request->photo
                    );
                    foreach ($combined_order->orders as $order) {
                        $order->manual_payment = 1;
                        $order->manual_payment_data = json_encode($manual_payment_data);
                        $order->save();
                    }
                    flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
                    return redirect()->route('order_confirmed');
                }
            }
        // } else {
        //     flash(translate('Select Payment Option.'))->warning();
        //     return back();
        // }
    }

    //redirects to this method after a successfull checkout
    public function checkout_done($combined_order_id, $payment)
    {
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            if($order->payment_type == 'Tabby') {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'pending';
                $order->payment_details = $payment;
                $order->save();
            }else {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'paid';
                $order->payment_details = $payment;
                $order->save();
            }


            // calculateCommissionAffilationClubPoint($order);
        }

        if(get_setting('google_analytics_enabled')) {
            GA4::postEvent([
                'name' => 'purchase',
                'params' => [
                    'currency' => currency_symbol(),
                    'value'    => $combined_order->grand_total,
                ],
            ]);
        }

        Session::put('combined_order_id', $combined_order_id);
        return redirect()->route('order_confirmed');
    }

    public function get_shipping_info(Request $request)
    {
        $carts = Cart::where('user_id', Auth::user()->id)->get();

        //        if (Session::has('cart') && count(Session::get('cart')) > 0) {
        if ($carts && count($carts) > 0) {
            $categories = Category::all();
            if(get_setting('google_analytics_enabled')) {
                GA4::postEvent([
                'name' => 'add_shipping_info',
                ]);
            }
            return view('frontend.shipping_info', compact('categories', 'carts'));
        }
        flash(translate('Your cart is empty'))->success();
        return back();
    }

    public function store_shipping_info(Request $request)
    {
        if ($request->address_id == null && $request->pickup_point_id == null) {
            flash(translate("Please add shipping address or pickup point"))->warning();
            return back();
        }
        // else if($request->shipping_company == null) {
        //     flash(translate("Please select shipping type"))->warning();
        //     return back();
        // }

        $carts = Cart::where('user_id', Auth::user()->id)->get();
        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        foreach ($carts as $key => $cartItem) {
            if($request->address_id != null) {
                $cartItem->address_id = $request->address_id;
                $cartItem->shipping_type = 'home_delivery';
            } else {
                if($request->pickup_point_id != null) {
                    // return dd($request->pickup_point_id);
                    $cartItem->pickup_point = $request->pickup_point_id;
                    $cartItem->shipping_type = 'pickup_point';
                }
            }
            // $cartItem->address_id = $request->address_id;
            //     $cartItem->shipping_company = $request->shipping_company;
            //     if($request->shipping_company == "mashkor") {
            //         $cartItem->shipping_cost = env("MASHKOR_PRICE");
            //     }else if($request->shipping_company == "quick") {
            //         $cartItem->shipping_cost = env("QUICK_PRICE");
            //     }else if($request->shipping_company == "armada") {
            //         $cartItem->shipping_cost = env("ARMADA_PRICE");
            //     }
            $cartItem->save();
        }

        $carrier_list = array();
        if (get_setting('shipping_type') == 'carrier_wise_shipping') {
            $zone = \App\Models\Country::where('id', $carts[0]['address']['country_id'])->first()->zone_id;

            $carrier_query = Carrier::query();
            $carrier_query->whereIn('id', function ($query) use ($zone) {
                $query->select('carrier_id')->from('carrier_range_prices')
                    ->where('zone_id', $zone);
            })->orWhere('free_shipping', 1);
            $carrier_list = $carrier_query->get();
        }

        //return view('frontend.delivery_info', compact('carts', 'carrier_list'));
    }

    //new shipping info
    public function shipping_info(Request $request)
    {
        $carts = Cart::where('user_id', Auth::user()->id)->get();
        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $categories = Category::all();
        return view('frontend.shipping_info', compact('categories', 'carts'));
    }

    public function store_delivery_info(Request $request)
    {
        $this->store_shipping_info($request);
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();

        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;


        if ($carts && count($carts) > 0) {
            foreach ($carts as $key => $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];

                if (get_setting('shipping_type') != 'carrier_wise_shipping' || $request['shipping_type_' . $product->user_id] == 'pickup_point') {
                    if ($request['shipping_type_' . $product->user_id] == 'pickup_point') {
                        $cartItem['shipping_type'] = 'pickup_point';
                        // $cartItem['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
                        $cartItem['pickup_point'] = $request['pickup_point_id'];

                    } else {
                        $cartItem['shipping_type'] = 'home_delivery';
                    }
                    $cartItem['shipping_cost'] = 0;
                    if ($cartItem['shipping_type'] == 'home_delivery') {
                        $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                    }
                } else {
                    $cartItem['shipping_type'] = 'carrier';
                    $cartItem['carrier_id'] = $request['carrier_id_' . $product->user_id];
                    $cartItem['shipping_cost'] = getShippingCost($carts, $key, $cartItem['carrier_id']);
                }

                //  $shipping += $cartItem['shipping_cost'];
                // if($cartItem['shipping_company'] == "mashkor") {
                //     $cartItem['shipping_cost'] = env("MASHKOR_PRICE");
                // }else if($cartItem['shipping_company'] == "quick") {
                //     $cartItem['shipping_cost'] = env("QUICK_PRICE");
                // }else if($cartItem['shipping_company'] == "armada") {
                //     $cartItem['shipping_cost'] = env("ARMADA_PRICE");
                // }
                // $shipping += $cartItem['shipping_cost'];

                // if total of order > get_setting('free_delivery_after') will set shipping is 0
                if ($subtotal > get_setting('free_delivery_after') && get_setting('free_delivery_after_enabled')) {
                    $cartItem['shipping_cost'] = 0;
                    $shipping = 0;
                }

                $cartItem->save();
            }
            foreach ($carts as $key => $cartItem) {
                // if total of order > get_setting('free_delivery_after') will set shipping is 0
                if($subtotal >= get_setting('free_delivery_after') && get_setting('free_delivery_after_enabled')) {
                    $cartItem['shipping_cost'] = 0;
                    $cartItem->save();
                }
            }
            $total = $subtotal + $tax + $shipping;
            // return dd($carts);

            $wallet_balance = Auth::user()->balance;
            if($wallet_balance > $total){
                $remaining_price = $wallet_balance - $total ;
            }else{
                $remaining_price = $total - $wallet_balance;

            }

            return view('frontend.payment_select', compact('carts', 'shipping_info', 'total','remaining_price'));
        } else {
            flash(translate('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }

       public function apply_coupon_code(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
        $response_message = array();

        if ($coupon != null) {



            if (CouponUsage::where('coupon_id', $coupon->id)->exists() && $coupon->only_one_use == 1) {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('This coupon has already been used and is no longer valid!');
            }
            elseif (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                    $coupon_details = json_decode($coupon->details);

                    $carts = Cart::where('user_id', Auth::user()->id)
                        ->where('owner_id', $coupon->user_id)
                        ->get();

                    $coupon_discount = 0;

                    if ($coupon->type == 'cart_base') {
                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                            $shipping += $cartItem['shipping_cost'];
                        }

                        // if total of order > get_setting('free_delivery_after') will set shipping is 0
                        if ($subtotal > get_setting('free_delivery_after') && get_setting('free_delivery_after_enabled')) {
                            $shipping = 0;
                        }

                        $sum = $subtotal + $tax + $shipping;

                        if ($sum >= $coupon_details->min_buy) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                            }
                        }
                    } elseif ($coupon->type == 'product_base') {
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                    }
                                }
                            }
                        }
                    } elseif ($coupon->type == 'category_base') {
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            if ($product->category != null) {
                                foreach ($coupon_details as $key => $coupon_detail) {
                                    if ($coupon_detail->category_id == $product->category_id) {
                                        // Log::alert($coupon_detail->excluded_product_ids);
                                        // Log::alert($cartItem['product_id']);
                                        if(isset($coupon_detail->excluded_product_ids)) {
                                            if (!in_array($cartItem['product_id'], $coupon_detail->excluded_product_ids)) { // Check if product is not excluded
                                                if ($coupon->discount_type == 'percent') {
                                                    $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                                } elseif ($coupon->discount_type == 'amount') {
                                                    $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                                }
                                            }
                                        }else{
                                            if ($coupon->discount_type == 'percent') {
                                                $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                            } elseif ($coupon->discount_type == 'amount') {
                                                $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } elseif ($coupon->type == 'brand_base') {
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            if ($product->brand_id != null) {
                                foreach ($coupon_details as $key => $coupon_detail) {
                                    if ($coupon_detail->brand_id == $product->brand_id) {
                                        if(isset($coupon_detail->excluded_product_ids)) {
                                            if (!in_array($cartItem['product_id'], $coupon_detail->excluded_product_ids)) { // Check if product is not excluded
                                                if ($coupon->discount_type == 'percent') {
                                                    $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                                } elseif ($coupon->discount_type == 'amount') {
                                                    $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                                }
                                            }
                                        }else{
                                            if ($coupon->discount_type == 'percent') {
                                                $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                            } elseif ($coupon->discount_type == 'amount') {
                                                $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($coupon_discount > 0) {
                        $cart = Cart::where('user_id', Auth::user()->id)
                            ->where('owner_id', $coupon->user_id)
                            ->update(
                                [
                                    'discount' => $coupon_discount / count($carts),
                                    'coupon_code' => $request->code,
                                    'coupon_applied' => 1
                                ]
                            );
                        // Log::info($cart);
                        // Log::alert("Coupon Code: ".$request->code . " Discount: ".$coupon_discount);
                        $response_message['response'] = 'success';
                        $response_message['message'] = translate('Coupon has been applied');

                        CouponUsage::create([
                            'user_id' => Auth::user()->id,
                            'coupon_id' => $coupon->id
                        ]);

                    } else {
                        $response_message['response'] = 'warning';
                        $response_message['message'] = translate('This coupon is not applicable to your cart products!');
                    }
            } else {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('Coupon expired!');
            }
        } else {
            $response_message['response'] = 'danger';
            $response_message['message'] = translate('Invalid coupon!');
        }

        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        $returnHTML = view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'))->render();
        return response()->json(array('response_message' => $response_message, 'html' => $returnHTML));
    }

    public function apply_coupon_code_guest(Request $request)
    {
        $coupon = Coupon::where('code', $request->code)->first();
        $response_message = array();

        if ($coupon != null) {
            if (CouponUsage::where('coupon_id', $coupon->id)->exists() && $coupon->only_one_use == 1) {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('This coupon has already been used and cannot be applied!');
                return response()->json(array('response_message' => $response_message));
            }

            if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                    $coupon_details = json_decode($coupon->details);

                    $carts = Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
                        ->where('owner_id', $coupon->user_id)
                        ->get();

                    $coupon_discount = 0;

                    if ($coupon->type == 'cart_base') {
                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                            $shipping += $cartItem['shipping_cost'];
                        }

                        // if total of order > get_setting('free_delivery_after') will set shipping is 0
                        if ($subtotal > get_setting('free_delivery_after') && get_setting('free_delivery_after_enabled')) {
                            $shipping = 0;
                        }

                        $sum = $subtotal + $tax + $shipping;
                        // convert $coupon_details->min_buy string to int
                        if (is_object($coupon_details)) {
                            $coupon_details->min_buy = (int) $coupon_details->min_buy;
                            $coupon_details->max_discount = (int) $coupon_details->max_discount;
                        } else {
                            // Handle the error, e.g., return a response or log the issue
                            Log::error("Coupon details not found or invalid.");
                        }
                        // Log::alert($coupon_details);
                        if ($sum >= $coupon_details->min_buy) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                            }
                        }
                    } elseif ($coupon->type == 'product_base') {
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['product_id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                    }
                                }
                            }
                        }
                    } elseif ($coupon->type == 'category_base') {
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            if ($product->category != null) {
                                foreach ($coupon_details as $key => $coupon_detail) {
                                    if ($coupon_detail->category_id == $product->category_id) {
                                        // Log::alert($coupon_detail->excluded_product_ids);
                                        // Log::alert($cartItem['product_id']);
                                        if(isset($coupon_detail->excluded_product_ids)) {
                                            if (!in_array($cartItem['product_id'], $coupon_detail->excluded_product_ids)) { // Check if product is not excluded
                                                if ($coupon->discount_type == 'percent') {
                                                    $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                                } elseif ($coupon->discount_type == 'amount') {
                                                    $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                                }
                                            }
                                        }else{
                                            if ($coupon->discount_type == 'percent') {
                                                $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                            } elseif ($coupon->discount_type == 'amount') {
                                                $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } elseif ($coupon->type == 'brand_base') {
                        foreach ($carts as $key => $cartItem) {
                            $product = Product::find($cartItem['product_id']);
                            if ($product->brand_id != null) {
                                foreach ($coupon_details as $key => $coupon_detail) {
                                    if ($coupon_detail->brand_id == $product->brand_id) {
                                        if(isset($coupon_detail->excluded_product_ids)) {
                                            if (!in_array($cartItem['product_id'], $coupon_detail->excluded_product_ids)) { // Check if product is not excluded
                                                if ($coupon->discount_type == 'percent') {
                                                    $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                                } elseif ($coupon->discount_type == 'amount') {
                                                    $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                                }
                                            }
                                        }else{
                                            if ($coupon->discount_type == 'percent') {
                                                $coupon_discount += (cart_product_price($cartItem, $product, false, false) * $coupon->discount / 100) * $cartItem['quantity'];
                                            } elseif ($coupon->discount_type == 'amount') {
                                                $coupon_discount += $coupon->discount * $cartItem['quantity'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($coupon_discount > 0) {
                        Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
                            ->where('owner_id', $coupon->user_id)
                            ->update(
                                [
                                    'discount' => $coupon_discount / count($carts),
                                    'coupon_code' => $request->code,
                                    'coupon_applied' => 1
                                ]
                            );
                        $response_message['response'] = 'success';
                        $response_message['message'] = translate('Coupon has been applied');

                        CouponUsage::create([
                            'user_id' => 0,
                            'coupon_id' => $coupon->id,
                            'guest_id' => Guest::where('temp_user_id', $request->session()->get('temp_user_id'))->first()->id
                        ]);

                    } else {
                        $response_message['response'] = 'warning';
                        $response_message['message'] = translate('This coupon is not applicable to your cart products!');
                    }
            } else {
                $response_message['response'] = 'warning';
                $response_message['message'] = translate('Coupon expired!');
            }
        } else {
            $response_message['response'] = 'danger';
            $response_message['message'] = translate('Invalid coupon!');
        }

        $carts = Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
            ->get();
        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        $returnHTML = view('frontend.partials.cart_summary_guest', compact('coupon', 'carts', 'shipping_info'))->render();
        return response()->json(array('response_message' => $response_message, 'html' => $returnHTML));
    }

    public function remove_coupon_code(Request $request)
    {
        if(Auth::user() == null) {
            $guest = Guest::where('temp_user_id', $request->session()->get('temp_user_id'))->first();
            Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
                ->update(
                    [
                        'discount' => 0.00,
                        'coupon_code' => '',
                        'coupon_applied' => 0
                    ]
                );
        } else {
            Cart::where('user_id', Auth::user()->id)
            ->update(
                [
                    'discount' => 0.00,
                    'coupon_code' => '',
                    'coupon_applied' => 0
                ]
            );
            CouponUsage::where('user_id', Auth::user()->id)
                ->where('coupon_id', Coupon::where('code', $request->coupon_code)->first()->id)
                ->delete();
        }

        $coupon = Coupon::where('code', $request->code)->first();
        $carts = [];
        if(Auth::user() == null) {
            $carts = Cart::where('temp_user_id', $request->session()->get('temp_user_id'))
                ->get();
        } else {
            $carts = Cart::where('user_id', Auth::user()->id)
                ->get();
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();

        return view('frontend.partials.cart_summary', compact('coupon', 'carts', 'shipping_info'));
    }

    public function apply_club_point(Request $request)
    {
        if (addon_is_activated('club_point')) {

            $point = $request->point;

            if (Auth::user()->point_balance >= $point) {
                $request->session()->put('club_point', $point);
                flash(translate('Point has been redeemed'))->success();
            } else {
                flash(translate('Invalid point!'))->warning();
            }
        }
        return back();
    }

    public function remove_club_point(Request $request)
    {
        $request->session()->forget('club_point');
        return back();
    }

    public function order_confirmed()
    {
        $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

        Cart::where('user_id', $combined_order->user_id)
            ->delete();

        //Session::forget('club_point');
        //Session::forget('combined_order_id');

        foreach ($combined_order->orders as $order) {
            NotificationUtility::sendOrderPlacedNotification($order);
        }

        return view('frontend.order_confirmed', compact('combined_order'));
    }


    //Start Checkout As Guest
    public function guest(Request $request)
    {
        $temp_user_id = $request->session()->get('temp_user_id');
        // $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];

        if ($carts && count($carts) > 0) {
            $categories = Category::all();
            return view('frontend.shipping_info_guest', compact('categories', 'carts'));
        }
        flash(translate('Your cart is empty'))->success();
        return back();
    }

    public function store_shipping_info_guest(Request $request)
    {

        $temp_user_id = $request->session()->get('temp_user_id');
        // $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        $guest = Guest::where('temp_user_id', $temp_user_id)->first();
        $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
        $shipping_type = $request['shipping_type'];

        // Check if guest exists
        if ($guest) {
            if($shipping_type == "home_delivery") {
                // Guest exists, check if the guest has an address
                $address = Address::where('guest_id', $guest->id)->first();

                if ($address) {
                    // Guest has an existing address, update it
                    $guest->temp_user_id = $request->session()->get('temp_user_id');
                    $guest->name = $request->name;
                    $guest->email = $request->email;
                    $guest->address = $shipping_type == "home_delivery" ? $request->address : '';
                    $guest->phone = $request->phone;
                    $guest->country_id = $shipping_type == "home_delivery" ? $request->country_id : '';
                    $guest->city_id = $shipping_type == "home_delivery" ? $request->city_id : '';
                    $guest->state_id = $shipping_type == "home_delivery" ? $request->state_id : '';
                    $guest->postal_code = $shipping_type == "home_delivery" ? $request->postal_code : '';
                    $guest->longitude = $shipping_type == "home_delivery" ? $request->longitude : '';
                    $guest->latitude = $shipping_type == "home_delivery" ? $request->latitude : '';
                    $guest->bloc = $shipping_type == "home_delivery" ? $request->bloc : '';
                    $guest->avenue = $shipping_type == "home_delivery" ? $request->avenue : '';
                    $guest->street = $shipping_type == "home_delivery" ? $request->street : '';
                    $guest->house = $shipping_type == "home_delivery" ? $request->house : '';
                    $guest->save();

                    $address->guest_id = $guest->id;
                    $address->address = $shipping_type == "home_delivery" ? $request->address : '';
                    $address->address_label = $shipping_type == "home_delivery" ? $request->address_label : '';
                    $address->phone = $request->phone;
                    $address->set_default = 1;
                    $address->country_id = $shipping_type == "home_delivery" ? $request->country_id : '';
                    $address->city_id = $shipping_type == "home_delivery" ? $request->city_id : '';
                    $address->state_id = $shipping_type == "home_delivery" ? $request->state_id : '';
                    $address->postal_code = $shipping_type == "home_delivery" ? $request->postal_code : '';
                    $address->longitude = $shipping_type == "home_delivery" ? $request->longitude : '';
                    $address->latitude = $shipping_type == "home_delivery" ? $request->latitude : '';
                    $address->bloc = $shipping_type == "home_delivery" ? $request->bloc : '';
                    $address->avenue = $shipping_type == "home_delivery" ? $request->avenue : '';
                    $address->street = $shipping_type == "home_delivery" ? $request->street : '';
                    $address->house = $shipping_type == "home_delivery" ? $request->house : '';
                    $address->address_type = $shipping_type == "home_delivery" ? $request->selected_type : '';
                    $address->building_name = $shipping_type == "home_delivery" ? $request->building_name : '';
                    $address->apt_number = $shipping_type == "home_delivery" ? $request->apt_number : '';
                    $address->building_number = $shipping_type == "home_delivery" ? $request->building_number : '';
                    $address->floor = $shipping_type == "home_delivery" ? $request->floor : '';
                    $address->save();
                } else {
                    // Guest has no address, create a new address
                    if($shipping_type == "home_delivery") {
                        $address = new Address();
                        $address->guest_id = $guest->id;
                        $address->address = $shipping_type == "home_delivery" ? $request->address : '';
                        $address->address_label = $shipping_type == "home_delivery" ? $request->address_label : '';
                        $address->phone = $request->phone;
                        $address->set_default = 1;
                        $address->country_id = $shipping_type == "home_delivery" ? $request->country_id : '';
                        $address->city_id = $shipping_type == "home_delivery" ? $request->city_id : '';
                        $address->state_id = $shipping_type == "home_delivery" ? $request->state_id : '';
                        $address->postal_code = $shipping_type == "home_delivery" ? $request->postal_code : '';
                        $address->longitude = $shipping_type == "home_delivery" ? $request->longitude : '';
                        $address->latitude = $shipping_type == "home_delivery" ? $request->latitude : '';
                        $address->bloc = $shipping_type == "home_delivery" ? $request->bloc : '';
                        $address->avenue = $shipping_type == "home_delivery" ? $request->avenue : '';
                        $address->street = $shipping_type == "home_delivery" ? $request->street : '';
                        $address->house = $shipping_type == "home_delivery" ? $request->house : '';
                        $address->address_type = $shipping_type == "home_delivery" ? $request->selected_type : '';
                        $address->building_name = $shipping_type == "home_delivery" ? $request->building_name : '';
                        $address->apt_number = $shipping_type == "home_delivery" ? $request->apt_number : '';
                        $address->building_number = $shipping_type == "home_delivery" ? $request->building_number : '';
                        $address->floor = $shipping_type == "home_delivery" ? $request->floor : '';
                        $address->save();
                    }
                }
            } else {
                $guest->temp_user_id = $request->session()->get('temp_user_id');
                $guest->name = $request->name;
                $guest->email = $request->email;
                $guest->phone = $request->phone;
                $guest->save();
            }
        } else {
            // Guest does not exist, create a new guest and address
            $guest = new Guest();
            $guest->temp_user_id = $request->session()->get('temp_user_id');
            $guest->name = $request->name;
            $guest->email = $request->email;
            $guest->phone = $request->phone;
            $guest->address = $shipping_type == "home_delivery" ? $request->address : '';
            $guest->country_id = $shipping_type == "home_delivery" ? $request->country_id : '';
            $guest->city_id = $shipping_type == "home_delivery" ? $request->city_id : '';
            $guest->state_id = $shipping_type == "home_delivery" ? $request->state_id : '';
            $guest->postal_code = $shipping_type == "home_delivery" ? $request->postal_code : '';
            $guest->longitude = $shipping_type == "home_delivery" ? $request->longitude : '';
            $guest->latitude = $shipping_type == "home_delivery" ? $request->latitude : '';
            $guest->bloc = $shipping_type == "home_delivery" ? $request->bloc : '';
            $guest->avenue = $shipping_type == "home_delivery" ? $request->avenue : '';
            $guest->street = $shipping_type == "home_delivery" ? $request->street : '';
            $guest->house = $shipping_type == "home_delivery" ? $request->house : '';
            $guest->save();

            if($shipping_type == "home_delivery") {
                $address = new Address();
                $address->guest_id = $guest->id;
                $address->address = $shipping_type == "home_delivery" ? $request->address : '';
                $address->address_label = $shipping_type == "home_delivery" ? $request->address_label : '';
                $address->phone = $request->phone;
                $address->set_default = 1;
                $address->country_id = $shipping_type == "home_delivery" ? $request->country_id : '';
                $address->city_id = $shipping_type == "home_delivery" ? $request->city_id : '';
                $address->state_id = $shipping_type == "home_delivery" ? $request->state_id : '';
                $address->postal_code = $shipping_type == "home_delivery" ? $request->postal_code : '';
                $address->longitude = $shipping_type == "home_delivery" ? $request->longitude : '';
                $address->latitude = $shipping_type == "home_delivery" ? $request->latitude : '';
                $address->bloc = $shipping_type == "home_delivery" ? $request->bloc : '';
                $address->avenue = $shipping_type == "home_delivery" ? $request->avenue : '';
                $address->street = $shipping_type == "home_delivery" ? $request->street : '';
                $address->house = $shipping_type == "home_delivery" ? $request->house : '';
                $address->address_type = $shipping_type == "home_delivery" ? $request->selected_type : '';
                $address->building_name = $shipping_type == "home_delivery" ? $request->building_name : '';
                $address->apt_number = $shipping_type == "home_delivery" ? $request->apt_number : '';
                $address->building_number = $shipping_type == "home_delivery" ? $request->building_number : '';
                $address->floor = $shipping_type == "home_delivery" ? $request->floor : '';
                $address->save();
            }
        }

        foreach ($carts as $key => $cartItem) {
            if($shipping_type == "home_delivery") {
                $cartItem->address_id = $address->id;
                $cartItem->shipping_type = 'home_delivery';
            } else {
                $cartItem->pickup_point = $request['pickup_point_id'];
                $cartItem->shipping_type = 'pickup_point';
            }
            $cartItem->save();
        }
    }

    public function store_delivery_info_guest(Request $request)
    {
        $this->store_shipping_info_guest($request);
        $temp_user_id = $request->session()->get('temp_user_id');
        // $carts = Cart::where('temp_user_id', $temp_user_id)->get();
        $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];

        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        $shipping_info = Address::where('id', $carts[0]['address_id'])->first();
        $total = 0;
        $tax = 0;
        $shipping = 0;
        $subtotal = 0;

        // return dd($carts);

        if ($carts && count($carts) > 0) {
            foreach ($carts as $key => $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $subtotal += $cartItem['price'] * $cartItem['quantity'];

                if ($request['shipping_type'] == 'pickup_point') {
                    $cartItem['shipping_type'] = 'pickup_point';
                    $cartItem['pickup_point'] = $request['pickup_point_id'];
                } else {
                    $cartItem['shipping_type'] = 'home_delivery';
                }
                $cartItem['shipping_cost'] = 0;
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    $cartItem['shipping_cost'] = getShippingCost($carts, $key);
                }

                if (isset($cartItem['shipping_cost']) && is_array(json_decode($cartItem['shipping_cost'], true))) {

                    foreach (json_decode($cartItem['shipping_cost'], true) as $shipping_region => $val) {
                        if ($shipping_info['city'] == $shipping_region) {
                            $cartItem['shipping_cost'] = (float)($val);
                            break;
                        } else {
                            $cartItem['shipping_cost'] = 0;
                        }
                    }
                } else {
                    if (
                        !$cartItem['shipping_cost'] ||
                        $cartItem['shipping_cost'] == null ||
                        $cartItem['shipping_cost'] == 'null'
                    ) {

                        $cartItem['shipping_cost'] = 0;
                    }
                }

                if ($product->is_quantity_multiplied == 1 && get_setting('shipping_type') == 'product_wise_shipping') {
                    $cartItem['shipping_cost'] =  $cartItem['shipping_cost'] * $cartItem['quantity'];
                }

                $shipping += $cartItem['shipping_cost'];
                $cartItem->save();
            }
            foreach ($carts as $key => $cartItem) {
                // if total of order > get_setting('free_delivery_after') will set shipping is 0
                if($subtotal >= get_setting('free_delivery_after') && get_setting('free_delivery_after_enabled')) {
                    $cartItem['shipping_cost'] = 0;
                    $cartItem->save();
                }
            }
            $total = $subtotal + $tax + $shipping;
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = ($temp_user_id != null) ? Cart::where('temp_user_id', $temp_user_id)->get() : [];
            return view('frontend.payment_select_guest', compact('carts', 'shipping_info', 'total'));
        } else {
            flash(translate('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }

    //check the selected payment gateway and redirect to that controller accordingly
    public function checkout_guest(Request $request)
    {
        $temp_user_id = $request->session()->get('temp_user_id');
        // Minumum order amount check
        if (get_setting('minimum_order_amount_check') == 1) {
            $subtotal = 0;
            foreach (Cart::where('temp_user_id', $temp_user_id)->get() as $key => $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                foreach(json_decode($cartItem['addons']) as $addon) {
                    if($addon->quantity > 0) {
                        $subtotal += $addon->price * $addon->quantity;
                    }
                }
            }
            if ($subtotal < get_setting('minimum_order_amount')) {
                flash(translate('You order amount is less then the minimum order amount'))->warning();
                return redirect()->route('home');
            }
        }
        // Minumum order amount check end

        if ($request->payment_option != null) {

            $carts = Cart::where('temp_user_id', $temp_user_id)
                ->get();

            if ($carts->isEmpty()) {
                flash(translate('Your cart is empty'))->warning();
                return redirect()->route('home');
            }

            if($carts[0]['address_id'] != 0) {
                $address = Address::where('id', $carts[0]['address_id'])->first();

                if ($address->city == null) {
                    flash(translate('Please select your city'))->warning();
                    return redirect()->route('store_shipping_info.store_guest');
                }
            } else {
                if($carts[0]['pickup_point'] == null || $carts[0]['pickup_point'] == 0) {
                    flash(translate('Please select your pickup point'))->warning();
                    return redirect()->route('store_shipping_info.store_guest');
                }
            }

            // return dd($request);
            (new OrderController)->guest_store($request, false);

            $request->session()->put('payment_type', 'cart_payment');

            //$data['combined_order_id'] = $request->session()->get('combined_order_id');
            //$request->session()->put('payment_data', $data);

            //if ($request->session()->get('combined_order_id') != null) {

            // If block for Online payment, wallet and cash on delivery. Else block for Offline payment
            $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
            if (class_exists($decorator)) {
                //return dd($decorator);
                $controller = app()->make($decorator);
                return $controller->pay($request);
            } else {
                $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                $manual_payment_data = array(
                    'name'   => $request->payment_option,
                    'amount' => $combined_order->grand_total,
                    'trx_id' => $request->trx_id,
                    'photo'  => $request->photo
                );
                foreach ($combined_order->orders as $order) {
                    $order->manual_payment = 1;
                    $order->manual_payment_data = json_encode($manual_payment_data);
                    $order->save();
                }
                flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
                return redirect()->route('order_confirmed_guest');
            }
        }
        // } else {
        //     flash(translate('Select Payment Option.'))->warning();
        //     return back();
        // }
    }

    public function order_confirmed_guest(Request $request)
    {

        $temp_user_id = $request->session()->get('temp_user_id');
        $order = Order::findOrFail(Session::get('order_id'));

        Cart::where('owner_id', $order->seller_id)
            ->where('temp_user_id', $temp_user_id)
            ->delete();

        return view('frontend.order_confirmed_guest', compact('order'));
    }

    //redirects to this method after a successfull checkout
    public function checkout_done_guest($combined_order_id, $payment)
    {
        //return dd($combined_order_id, $payment);
        $combined_order = CombinedOrder::findOrFail($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order = Order::findOrFail($order->id);
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();

            // calculateCommissionAffilationClubPoint($order);
        }

        Session::put('combined_order_id', $combined_order_id);
        redirect()->route('order_confirmed_guest');
    }
    //End CheckOut As Guest
}
