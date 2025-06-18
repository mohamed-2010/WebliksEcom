<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Http\Controllers\WalletController;
use App\Models\Cart;
use App\Models\Guest;
use App\Models\Order;
use App\Models\Wallet;
use App\Utility\NotificationUtility;
use Auth;
use Illuminate\Support\Facades\Session;

class RechargeupayController extends Controller {
    public function pay(Request $request){
        if(Auth::user() != null) {
            if (Auth::user()->phone == null) {
                flash('Please add phone number to your profile')->warning();
                return redirect()->route('profile');
            }

            if (Auth::user()->email == null) {
                $email = 'customer@example.com';
            }
            else{
                $email = Auth::user()->email;
            }
        }else{
            $email = 'customer@example.com';
        }

        $url = env('UPAYMENT_URL');

        // Get the amount from the session
        if (Session::get('payment_type') == 'wallet_payment') {
        $amount = round(Session::get('payment_data')['amount'], 3);
        }
        Session::put('payment_amount', $amount);

        $rnd = random_int(1000000000000000, 9999999999999999);
        $hashMac = hash('sha512', "mer2300010|".$rnd."|".route('rechargeupay.success')."|".route('rechargeupay.fail')."|".$amount."|GEN"."|"."0589195"."|".$rnd, true);

        $hashMacHex = bin2hex($hashMac);

        $fields = array(
            "products" => array(
                array(
                    "name" => "Wallet Recharge",
                    "description" => "Wallet Recharge",
                    "price" => $amount,
                    "quantity" => 1
                ),
            ),
            "order" => array(
                "id" => (string) $rnd,
                "reference" => (string) $rnd,
                "description" => "Wallet Recharge",
                "currency" => "KWD",
                "amount" => $amount,
            ),
            "paymentGateway" => array(
                "src" => "knet"
            ),
            "language" => "en",
            "reference" => array(
                "id" => (string) $rnd,
            ),
            "customer" => array(
                "uniqueId" => Auth::user() != null ? (string)Auth::user()->id : "0",
                "name" => Auth::user() != null ? Auth::user()->name : "Customer",
                "email" => Auth::user() != null ? Auth::user()->email : "customer@example.com",
                "mobile" => Auth::user() != null ? Auth::user()->phone : "000000000"
            ),
            "returnUrl" => route('rechargeupay.success'),
            "cancelUrl" => route('rechargeupay.fail'),
            "notificationUrl" => route('rechargeupay.success'),
            "customerExtraData" => json_encode(["amount" => $amount])
        );

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . env('UPAYMENT_SECRET_KEY')
        );

        $fields_string = json_encode($fields);

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

        $url_forward = str_replace('"', '', stripslashes($result['data']['link']));

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

        if(Auth::user() == null) {
            flash(translate('Payment failed. User not authenticated.'))->error();
            return redirect()->route('home');
        }

        $amount = 0;
        if ($request->has('trn_udf')) {
            $trnUdfData = json_decode($request->input('trn_udf'), true);
            if (isset($trnUdfData['amount'])) {
                $amount = $trnUdfData['amount'];
            }
        }

        // If session payment_data exists, override its amount
        $payment_data = Session::get('payment_data', []);
        $payment_data['amount'] = $amount;


        $user = Auth::user();
        $user->balance = $user->balance + $payment_data['amount'];
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $payment_data['amount'];
        $wallet->payment_method = "Online Recharge";
        $wallet->payment_details = "Online Recharge";
        $wallet->approval = 1;
        $wallet->save();

        Session::forget('payment_data');
        Session::forget('payment_type');

        flash(translate('Payment completed'))->success();
        return redirect()->route('wallet.index');
    }


    public function fail(Request $request){
        flash(translate('Payment failed'))->error();
        return redirect()->route('wallet.index');
    }
}
