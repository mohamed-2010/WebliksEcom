<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\CombinedOrder;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShippmentController;
use App\Models\Cart;
use App\Models\Guest;
use App\Models\Order;
use App\Models\User;
use App\Utility\NotificationUtility;
use Auth;
use Illuminate\Support\Facades\Session;

class WalletupayController extends Controller {
    public function pay(){
        if(Auth::user() != null) {
            if (Auth::user()->phone == null) {
                flash('Please add phone number to your profile')->warning();
                return redirect()->route('profile');
            }

            if (Auth::user()->email == null) {
                $email = 'customer@exmaple.com';
            }
            else{
                $email = Auth::user()->email;
            }
        }else{
            $email = 'customer@exmaple.com';

        }

        $url = env('UPAYMENT_URL');

            $amount = 0;
            if(Session::has('payment_type')){
                if(Session::get('payment_type') == 'cart_payment'){
                    $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                    $price = round($combined_order->grand_total, 3);
                    $walletBalance=Auth::user()->balance;

                    $amount= $price - $walletBalance;
                }
                // elseif (Session::get('payment_type') == 'wallet_payment') {
                //     $amount = round(Session::get('payment_data')['amount'], 3);
                // }
                // elseif (Session::get('payment_type') == 'customer_package_payment') {
                //     $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
                //     $amount = round($customer_package->amount, 3);
                // }
                // elseif (Session::get('payment_type') == 'seller_package_payment') {
                //     $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
                //     $amount = round($seller_package->amoun, 3);
                // }
            }

            $rnd = random_int(1000000000000000, 9999999999999999);
            $hashMac = hash('sha512', "mer2300010|".$combined_order->id."|".route('bookeeypay.success')."|".route('bookeeypay.fail')."|".$amount."|GEN"."|"."0589195"."|".$rnd, true);

            $hashMacHex = bin2hex($hashMac);

            $fields = array(
                "products" => array(
                    array(
                        "name" => "Logitech K380",
                        "description" => "test",
                        "price" => $amount,
                        "quantity" => 1
                    ),
                ),
                "order" => array(
                    "id" => (string) $combined_order->id,
                    "reference" => (string) $combined_order->id,
                    "description" => "",
                    "currency" => "KWD",
                    "amount" => $amount,
                ),
                "paymentGateway" => array(
                    "src" => "knet"
                ),
                "language" => "en",
                "reference" => array(
                    "id" => (string) $combined_order->id,
                ),
                "customer" => array(
                    "uniqueId" => Auth::user() != null ? (string)Auth::user()->id : "0",
                    "name" => Auth::user() != null ? Auth::user()->name : "Customer",
                    "email" => Auth::user() != null ? Auth::user()->email : "customer@example.com",
                    "mobile" => Auth::user() != null ? Auth::user()->phone : "000000000"
                ),
                "returnUrl" => route('walletupay.success'),
                "cancelUrl" => route('walletupay.fail'),
                "notificationUrl" => route('walletupay.success'),
                "customerExtraData" => ""
            );

            $headers = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('UPAYMENT_SECRET_KEY')
            );

            $fields_string = json_encode($fields);

            //return dd($fields_string);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'content-type: application/json',
                'accept: application/json',
                'Authorization: Bearer ' . env('UPAYMENT_SECRET_KEY')
            ));

            $result = curl_exec($ch);

            $result = json_decode($result, true);

            // return dd([
            //     'header' => $headers,
            //     'request' => $fields,
            //     'response' => $result
            //     ]);

            $url_forward = str_replace('"', '', stripslashes($result['data']['link']));

         //   return dd($url_forward);

            $this->redirect_to_merchant($url_forward);
    }

    function redirect_to_merchant($url) {
        ?>
        <html xmlns="http://www.w3.org/1999/xhtml">
          <head><script type="text/javascript">
            function closethisasap() { document.forms["redirectpost"].submit(); }
          </script></head>
          <body onLoad="closethisasap();">

            <form name="redirectpost" method="post" action="<?php echo $url; ?>"></form>

          </body>
        </html>
        <?php
        exit;
    }


    public function success(Request $request) {
        $user = Auth::user();
        $walletBalance=Auth::user()->balance;
        $user->balance = 0;
        $user->save();
        //$payment_type = $request->opt_a;
        if(Auth::user() == null) {
            $request['payment_option'] = "upay";
            (new OrderController)->guest_store($request, true);
            $temp_user_id = Session::get('temp_user_id');
            $guest = Guest::where('temp_user_id', $temp_user_id)->first();
            //$order = Order::where('guest_id', $guest->id)->first();

            $combined_order = CombinedOrder::findOrFail(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id);
            $is_pickup_point = false;
            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'paid';
                $order->payment_details = json_encode($request->all());
                $order->save();

                if($order->pickup_point_id != null){
                    $is_pickup_point = true;
                }

                Cart::where('owner_id', $order->seller_id)
                ->where('temp_user_id', $temp_user_id)
                ->delete();
                // calculateCommissionAffilationClubPoint($order);
            }
            if(!$is_pickup_point) {
                $shippment = (new ShippmentController)->create_armada_delivery([
                    'order_id' => isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id,
                    'customer_name' => $guest->name,
                    'mobile_number' => $guest->phone,
                    'customer_area' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->state : "",
                    'street' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->street : "",
                    'building' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->building_number : "",
                    'block' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->bloc : "",
                    'amount_to_collect' => $combined_order->grand_total,
                    'payment_method' => 'upay'
                ]);
                if(isset($shippment->orderStatus) && $shippment->orderStatus != null) {
                    $order->shipping_company = "armada";
                    $order->traking_order_id = $shippment->code;
                    $order->save();
                }
            }
            NotificationUtility::sendNotification($order, $request->status);
            return view('frontend.order_confirmed_guest', compact('order'));
        }else{
            $request['payment_option'] = "Wallet_upay";
            (new OrderController)->store($request, true);
            $order = Order::where('user_id', Auth::user()->id)->first();

            $combined_order = CombinedOrder::findOrFail(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id);
            $is_pickup_point = false;

            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'paid';
                $order->wallet_balance = $walletBalance;
                $order->payment_details = json_encode($request->all());
                $order->save();

                if($order->pickup_point_id != null){
                    $is_pickup_point = true;
                }

                // calculateCommissionAffilationClubPoint($order);
            }
            if(!$is_pickup_point){
                (new ShippmentController)->create_armada_delivery([
                    'order_id' => isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id,
                    'customer_name' => Auth::user()->name,
                    'mobile_number' => Auth::user()->phone,
                    'customer_area' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->state : "",
                    'street' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->street : "",
                    'building' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->building_number : "",
                    'block' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->bloc : "",
                    'amount_to_collect' => $combined_order->grand_total,
                    'payment_method' => 'upay'
                ]);
            }

            Cart::where('owner_id', $order->seller_id)
                ->where('user_id', Auth::user()->id)
                ->delete();

            NotificationUtility::sendNotification($order, $request->status);
            $checkoutController = new CheckoutController;
            return $checkoutController->checkout_done(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id, json_encode($request->all()));
        }
    }


    public function fail(Request $request){
        flash(translate('Payment failed'))->error();
    	return redirect()->route('cart');
    }
}
