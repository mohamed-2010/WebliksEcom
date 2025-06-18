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
use App\Utility\NotificationUtility;
use Auth;
use Illuminate\Support\Facades\Session;

class BookeeypayCreditController extends Controller {
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

            // if (get_setting('bookeeypay_sandbox') == 1) {
            //     $url = 'https://demo.bookeey.com/pgapi/api/payment/requestLink';
            // }
            // else {
                $url = 'https://pg.bookeey.com/internalapi/api/payment/requestLink';
            //}

            $amount = 0;
            if(Session::has('payment_type')){
                if(Session::get('payment_type') == 'cart_payment'){
                    $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                    // $amount = round($combined_order->grand_total, 3);
                    $amount = 0;
                    if(Auth::user() == null) {
                        $guest_id = Session::get('temp_user_id');
                        $carts = Cart::where('temp_user_id', $guest_id)->get();
                    }else{
                        $carts = Cart::where('user_id', Auth::user()->id)->get();
                    }

                    // foreach ($carts as $key => $cart) {
                    //     $amount += $cart->price * $cart->quantity;
                    //     $amount += $cart->shipping_cost;
                    // }
                    $amount = calculateCartSubTotal($carts);

                    $amount = round($amount, 3);
                }
            }
            //return dd([$combined_order->id, Session::get('combined_order_id')]);
            $rnd = random_int(1000000000000000, 9999999999999999);
            $hashMac = hash('sha512', "mer2300010|".$combined_order->id."|".route('bookeeypay.success')."|".route('bookeeypay.fail')."|".$amount."|GEN"."|"."0589195"."|".$rnd, true);

            $hashMacHex = bin2hex($hashMac);
            $fields = array(
                "DBRqst" => "PY_ECom",
                "DO_Appinfo" => array(
                    "APIVer" => "",
                    "AppID" => "",
                    "APPTyp" => "",
                    "AppVer" => "",
                    "Country" => "",
                    "DevcType" => "5",
                    "HsCode" => "",
                    "IPAddrs" => "",
                    "MdlID" => "",
                    "OS" => "Android",
                    "UsrSessID" => ""
                ),
                "Do_MerchDtl" => array(
                    "BKY_PRDENUM" => "ECom",
                    "FURL" => route('bookeeypay.fail'),
                    // failure Url of leservices/
                    "MerchUID" => env('BOOKEEYPAY_MID'),
                    //leservice merchantId
                    "SURL" => route('bookeeypay.success'),
                    //leservice success url
                ),
                "Do_MoreDtl" => array(
                    "Cust_Data1" => "",
                    "Cust_Data2" => "",
                    "Cust_Data3" => ""
                ),
                "Do_PyrDtl" => array(
                    "Pyr_MPhone" => Auth::user() != null ? Auth::user()->phone : "000000000",
                    //customer phone
                    "Pyr_Name" => Auth::user() != null ? Auth::user()->name : "Customer",
                    //customer name
                ),
                "Do_TxnDtl" => [
                    array(
                        //leservice merchantId
                        "SubMerchUID" => env('BOOKEEYPAY_MID'),
                        "Txn_AMT" => $amount,
                        // amount customer has to pay
                    )
                    ],
                    "Do_TxnHdr" => array(
                        "BKY_Txn_UID" => "",
                        "Merch_Txn_UID" => $combined_order->id,
                        // unique ref no to track leservice and bookee
                        "PayFor" => "ECom",
                        //"PayMethod" => "",
                        "PayMethod" => "credit",
                        // payment Method it should be
                        //knet,credit,amex and bookeey
                        "Txn_HDR" => $rnd,
                        // unique ref no to trak leservice and bookeey
                        "hashMac" => $hashMacHex
                    )
            );

            $headers = array(
                'Content-Type: application/json',
                'secretkey: '.env('BOOKEEYPAY_SECRET_KEY'),
            );

            $fields_string = json_encode($fields);

           // return dd($fields_string);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($fields_string))
            );

            $result = curl_exec($ch);

            $result = json_decode($result, true);

            //return dd($result);

            $url_forward = str_replace('"', '', stripslashes($result['PayUrl']));

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

            <form name="redirectpost" method="get" action="<?php echo $url; ?>"></form>

          </body>
        </html>
        <?php
        exit;
    }


    public function success(Request $request) {
        //$payment_type = $request->opt_a;
        if(Auth::user() == null) {
            $request['payment_option'] = "bookeeypay_credit";
            (new OrderController)->guest_store($request, true);
            $temp_user_id = Session::get('temp_user_id');
            $guest = Guest::where('temp_user_id', $temp_user_id)->first();
            $order = Order::where('guest_id', $guest->id)->first();

            $combined_order = CombinedOrder::findOrFail($request->merchantTxnId);
            $is_pickup_point = false;

            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'paid';
                $order->payment_details = json_encode($request->all());
                $order->save();

                if($order->pickup_point_id != null){
                    $is_pickup_point = true;
                }

                // calculateCommissionAffilationClubPoint($order);
            }

            Cart::where('owner_id', $order->seller_id)
                ->where('temp_user_id', $temp_user_id)
                ->delete();
            if(!$is_pickup_point) {
                // $shippment = (new ShippmentController)->create_armada_delivery([
                //     'order_id' => $request->merchantTxnId,
                //     'customer_name' => $guest->name,
                //     'mobile_number' => $guest->phone,
                //     'customer_area' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->state : "",
                //     'street' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->street : "",
                //     'building' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->building_number : "",
                //     'block' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->bloc : "",
                //     'amount_to_collect' => $combined_order->grand_total,
                //     'payment_method' => 'bookeeypay_credit'
                // ]);
                // if($shippment->orderStatus != null) {
                //     $order->shipping_company = "armada";
                //     $order->traking_order_id = $shippment->code;
                //     $order->save();
                // }
            }
            NotificationUtility::sendNotification($order, $request->status);

            return view('frontend.order_confirmed_guest', compact('order'));
        }else{
            $request['payment_option'] = "bookeeypay_credit";
            (new OrderController)->store($request, true);
            $order = Order::where('user_id', Auth::user()->id)->first();

            $combined_order = CombinedOrder::findOrFail($request->merchantTxnId);
            $is_pickup_point = false;

            foreach ($combined_order->orders as $key => $order) {
                $order = Order::findOrFail($order->id);
                $order->payment_status = 'paid';
                $order->payment_details = json_encode($request->all());
                $order->save();

                if($order->pickup_point_id != null){
                    $is_pickup_point = true;
                }

                // calculateCommissionAffilationClubPoint($order);
            }

            Cart::where('owner_id', $order->seller_id)
                ->where('user_id', Auth::user()->id)
                ->delete();
            // if(!$is_pickup_point) {
            //     (new ShippmentController)->create_armada_delivery([
            //         'order_id' => $request->merchantTxnId,
            //         'customer_name' => Auth::user()->name,
            //         'mobile_number' => Auth::user()->phone,
            //         'customer_area' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->state : "",
            //         'street' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->street : "",
            //         'building' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->building_number : "",
            //         'block' => count($combined_order->orders) > 0 ? json_decode($combined_order->orders[0]->shipping_address)->bloc : "",
            //         'amount_to_collect' => $combined_order->grand_total,
            //         'payment_method' => 'bookeeypay_credit'
            //     ]);
            // }
            NotificationUtility::sendNotification($order, $request->status);
            $checkoutController = new CheckoutController;
            return $checkoutController->checkout_done($request->merchantTxnId, json_encode($request->all()));
        }
    }

    public function fail(Request $request){
        flash(translate('Payment failed'))->error();
    	return redirect()->route('cart');
    }
}
