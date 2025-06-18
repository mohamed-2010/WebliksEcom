<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Models\CombinedOrder;
use App\Http\Controllers\OrderController;
use App\Models\Cart;
use App\Models\Guest;
use App\Models\Order;
use App\Utility\NotificationUtility;
use Auth;
use Illuminate\Support\Facades\Session;

class RechargeBookeeypayController extends Controller {
    public function pay() {
        if (Auth::user() != null) {
            if (Auth::user()->phone == null) {
                flash('Please add phone number to your profile')->warning();
                return redirect()->route('profile');
            }
            $email = Auth::user()->email ?? 'customer@example.com';
        } else {
            $email = 'customer@example.com';
        }

        $url = 'https://pg.bookeey.com/internalapi/api/payment/requestLink';

        $amount = Session::get('payment_data')['amount'];


        $rnd = random_int(1000000000000000, 9999999999999999);
        $hashMac = hash('sha512', "mer2300010|" . Auth::user()->id . "|" . route('rechargebookeeypay.success') . "|" . route('rechargebookeeypay.fail') . "|" . $amount . "|GEN|0589195|" . $rnd, true);
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
                "FURL" => route('rechargebookeeypay.fail'),
                // failure Url of leservices/
                "MerchUID" => env('BOOKEEYPAY_MID'),
                //leservice merchantId
                "SURL" => route('rechargebookeeypay.success'),
                //leservice success url
            ),
            "Do_MoreDtl" => array(
                "Cust_Data1" => "",
                "Cust_Data2" => "",
                "Cust_Data3" => ""
            ),
            "Do_PyrDtl" => [
                "Pyr_MPhone" => Auth::user() ? Auth::user()->phone : "000000000",
                "Pyr_Name" => Auth::user() ? Auth::user()->name : "Customer",
            ],
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
                    "Merch_Txn_UID" => Auth::user()->id,
                    // unique ref no to track leservice and bookee
                    "PayFor" => "ECom",
                    //"PayMethod" => "",
                    "PayMethod" => "knet",
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

        $url_forward = str_replace('"', '', stripslashes($result['PayUrl']));

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

        $payment_data = Session::get('payment_data');

        if (!$payment_data || !isset($payment_data['amount'])) {
            flash(translate('Payment data is missing.'))->error();
            return redirect()->route('wallet.index');
        }

        $payment_details = json_encode($request->all());

        $walletController = new WalletController;
        $walletController->wallet_payment_done($payment_data, $payment_details);
    }

    public function fail(Request $request){
        flash(translate('Payment failed'))->error();
        return redirect()->route('wallet.index');
    }
}
