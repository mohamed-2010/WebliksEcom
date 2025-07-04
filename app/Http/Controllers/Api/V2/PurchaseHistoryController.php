<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\PurchasedResource;
use App\Http\Resources\V2\PurchaseHistoryMiniCollection;
use App\Http\Resources\V2\PurchaseHistoryCollection;
use App\Http\Resources\V2\PurchaseHistoryItemsCollection;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request)
    {
        $order_query = Order::query();
        if ($request->payment_status != "" || $request->payment_status != null) {
            $order_query->where('payment_status', $request->payment_status);
        }
        if ($request->delivery_status != "" || $request->delivery_status != null) {
            $delivery_status = $request->delivery_status;
            $order_query->whereIn("id", function ($query) use ($delivery_status) {
                $query->select('order_id')
                    ->from('order_details')
                    ->where('delivery_status', $delivery_status);
            });
        }

        if(auth()->user() != null) {
            return new PurchaseHistoryMiniCollection($order_query->where('user_id', auth()->user()->id)->latest()->paginate(5));
        } else {
            return new PurchaseHistoryMiniCollection($order_query->where('guest_id', $request->user_id)->latest()->paginate(5));
        }
    }

    public function details($id)
    {
        $order_detail = Order::where('id', $id)->where('user_id', auth()->user()->id)->get();
        // $order_query = auth()->user()->orders->where('id', $id);

        // return new PurchaseHistoryCollection($order_query->get());
        return new PurchaseHistoryCollection($order_detail);
    }

    public function items($id)
    {
        $order_id = Order::select('id')->where('id', $id)->where('user_id', auth()->user()->id)->first();
        $order_query = OrderDetail::where('order_id', $order_id->id);
        return new PurchaseHistoryItemsCollection($order_query->get());
    }

    public function digital_purchased_list()
    {


        $order_detail_products = OrderDetail::whereHas('order', function($q){
    		$q->where('payment_status', 'paid');
            $q->where('user_id', auth()->id());
		})->with(['product' => function($query){
            $query->where('digital', 1);
          }])
           ->paginate(15);
      
    //   $products = Product::with(['orderDetails', 'orderDetails.order' => function($q) {
    //          $q->where('payment_status', 'paid');
    //          $q->where('user_id', auth()->id());
    //     }])
    //     ->where('digital', 1)
    //     ->paginate(15);  



        return PurchasedResource::collection($order_detail_products);
    }
}
