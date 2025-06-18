<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\SellerPackage;
use App\Utility\NotificationUtility;
use Session;
use Auth;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function pay(Request $request)
    {
        if (Auth::guest()) {
            flash(translate("Sorry, guests can't use the wallet"))->error();
            return back();
        }

        if (Session::get('payment_type') == 'cart_payment') {
            $user = Auth::user();
            $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));

            if ($combined_order->grand_total < 0) {
                return $this->success($combined_order, $request);
            }

            if ($user->balance >= $combined_order->grand_total) {
                // Deduct balance from the user
                $user->balance -= $combined_order->grand_total;
                $user->save();

                // Call success method
                return $this->success($combined_order, $request);
            } else {
                flash(translate("Sorry! You don't have enough balance in your wallet."))->error();
                return back();
            }
        }

        flash(translate("Invalid payment type"))->error();
        return back();
    }

    public function success($combined_order, Request $request)
    {
        (new OrderController)->store($request, true);
        $order = Order::where('combined_order_id', $combined_order->id)->first();

        $order->wallet_balance = $order->grand_total;
        $order->save();

        // $order = Order::where('combined_order_id', $combined_order->id)->update([
        //     'payment_status' => 'paid'
        // ]);

        // Delete user's cart items
        Cart::where('user_id', Auth::id())->delete();

        NotificationUtility::sendNotification(request(), 'paid');

        // Redirect to order confirmation view
        $checkoutController = new CheckoutController;
        return $checkoutController->checkout_done($combined_order->id, json_encode($request->all()));

    }

    public function fail(Request $request) {
        flash(translate('Payment failed'))->error();
        return redirect()->route('cart');
    }
}
