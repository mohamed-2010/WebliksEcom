<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShippmentController;
use App\Models\Cart;
use App\Models\Order;
use App\Utility\NotificationUtility;
use Auth;
use Illuminate\Http\Request;
use Session;

class CashOnDeliveryController extends Controller
{
    public function pay(Request $request){
        flash(translate("Your order has been placed successfully"))->success();
        // return dd($request);
        if(Auth::user() == null) {
            (new OrderController)->guest_store($request, true);
            $temp_user_id = Session::get('temp_user_id');
            $order = Order::findOrFail(Session::get('order_id'));

            Cart::where('owner_id', $order->seller_id)
                ->where('temp_user_id', $temp_user_id)
                ->delete();
            
/*            $shippment = (new ShippmentController)->create_armada_delivery([
                'order_id' => $order->id,
                'customer_name' => json_decode($order->shipping_address)->name,
                'mobile_number' => json_decode($order->shipping_address)->phone,
                'customer_area' => json_decode($order->shipping_address)->state,
                'street' => json_decode($order->shipping_address)->street,
                'building' => json_decode($order->shipping_address)->building_number,
                'block' => json_decode($order->shipping_address)->bloc,
                'amount_to_collect' => $order->grand_total,
                'payment_method' => 'cash_on_delivery'
            ]);
            
            if($shippment->orderStatus != null) {
                $order->shipping_company = "armada";
                $order->traking_order_id = $shippment->code;
                $order->save();
            } 
*/
            NotificationUtility::sendNotification($order, $order->status);
            return view('frontend.order_confirmed_guest', compact('order'));
        }
        $order = (new OrderController)->store($request, true);
        NotificationUtility::sendNotification($request, $request->status);
        return redirect()->route('order_confirmed');
    }
}
