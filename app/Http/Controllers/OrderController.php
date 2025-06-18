<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Api\V2\ShippingController;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\OrderDetail;
use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\User;
use App\Models\CombinedOrder;
use App\Models\SmsTemplate;
use App\Models\Guest;
use Auth;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Models\BrancheUser;
use App\Utility\NotificationUtility;
// use CoreComponentRepository;
use App\Utility\SmsUtility;
use DB;
use Exception;
use Illuminate\Support\Facades\Route;

class OrderController extends Controller
{

    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_orders|view_inhouse_orders|view_seller_orders|view_pickup_point_orders'])->only('all_orders');
        $this->middleware(['permission:view_order_details'])->only('show');
        $this->middleware(['permission:delete_order'])->only('destroy');
    }

    // All Orders
    public function all_orders(Request $request)
    {
        // CoreComponentRepository::instantiateShopRepository();

        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';

        $orders = Order::orderBy('id', 'desc');
        $admin_user_id = User::where('user_type', 'admin')->first()->id;


        if (
            // Route::currentRouteName() == 'inhouse_orders.index' &&
            Auth::user()->can('view_inhouse_orders')
        ) {
            $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
        } else if (
            Route::currentRouteName() == 'seller_orders.index' &&
            Auth::user()->can('view_seller_orders')
        ) {
            $orders = $orders->where('orders.seller_id', '!=', $admin_user_id);
        } else if (
            Route::currentRouteName() == 'pick_up_point.index' &&
            Auth::user()->can('view_pickup_point_orders')
        ) {
            $orders->where('shipping_type', 'pickup_point')->orderBy('code', 'desc');
            if (
                Auth::user()->user_type == 'staff' &&
                Auth::user()->staff->pick_up_point != null
            ) {
                $orders->where('shipping_type', 'pickup_point')
                    ->where('pickup_point_id', Auth::user()->staff->pick_up_point->id);
            }
        } else if (
            Route::currentRouteName() == 'all_orders.index' &&
            Auth::user()->can('view_all_orders')
        ) {
        } else {
            abort(403);
        }

        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
        }
        if ($request->payment_status != null) {
            $orders = $orders->where('payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($date != null) {
            $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])) . '  00:00:00')
                ->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])) . '  23:59:59');
        }
        // if (Auth::user()->user_type == 'staff') {
        //     // Retrieve all branches assigned to the staff
        //     $branch_user = BrancheUser::with('branche')->with('branche.cities')->where('user_id', Auth::user()->id)->get();

        //     $cities_id = [];

        //     // Extract the IDs of the cities assigned to the staff
        //     foreach ($branch_user as $key => $value) {
        //         if($value->branche != null) {
        //             $cities_id[] = $value->branche->cities->pluck('id')->toArray();
        //         }
        //     }

        //     //return $cities_id;

        //     //$orders = $orders->where('shipping_address', 'like', '%' . $cities_id[0][0] . '%');
        //     foreach ($cities_id as $key => $value) {
        //         foreach ($value as $key => $value2) {
        //             $orders = $orders->orWhere('shipping_address', 'like', '%' . $value2 . '%');
        //         }
        //     }
        // }

        $orders = $orders->paginate(15);
        return view('backend.sales.index', compact('orders', 'sort_search', 'payment_status', 'delivery_status', 'date'));
    }

    public function show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        if($order_shipping_address) {
            $delivery_boys = User::where('city', $order_shipping_address->city)
                ->where('user_type', 'delivery_boy')
                ->get();
        }else{
            $delivery_boys = null;
        }

        $order->viewed = 1;
        $order->save();
        return view('backend.sales.show', compact('order', 'delivery_boys'));
    }

    public function destroy_item($id , $order1){

        $orderItem = OrderDetail::findOrFail($id);
        $order = Order::findOrFail($order1);
        $orderShaping = $orderItem->shipping_cost;
        $orderItems = OrderDetail::where('order_id',$order->id )->get();
        $totalOreder = 0;
        foreach ($orderItems as $orderItem1){
            $totalOreder +=  $orderItem1->price;
        }
        $couponDiscount = ($order->coupon_discount / $totalOreder) * 100;
        //        return  OrderDetail::where('order_id',692)->get();
        // $product_stock = ProductStock::where('product_id', $id)->where('variant', $orderItem->variation)->first();
        $product_stock = ProductStock::where('product_id',$orderItem->product_id)->where('variant', $orderItem->variation)->first();
        $product_discount = ($orderItem->price * $couponDiscount) / 100;
        $orderItemPrice = $orderItem->price - $product_discount;

        if ($product_stock != null) {
            $product_stock->qty += $orderItem->quantity;
            $product_stock->save();
        }

        //   return $order->grand_total = $order->grand_total - $orderItem->price ;
        $order->grand_total = $order->grand_total - $orderItemPrice ;
        $order->coupon_discount = $order->coupon_discount - $product_discount ;
        $order->save();
        $orderItem->delete();
        $orderItemOld = OrderDetail::where('order_id',$order1)->first();
        //return  OrderDetail::where('order_id',$order)->get();
        if($orderItemOld!= null){
            $orderItemOld['shipping_cost'] = $orderItemOld->shipping_cost + $orderShaping;
            $orderItemOld->save();
        }

        flash(translate('Item has been deleted successfully'))->success();
        return back();

    }

    public function add_item_order($idVariant,$idProduct,$idOrder){

        $product = Product::find($idProduct);
        $variant = ProductStock::findOrFail($idVariant);
        $order = Order::findOrFail($idOrder);

        // $response = array(
        //     'status' => 'success',
        //     'msg' =>$variant->variant,
        //     );

        //     return response()->json( $response );

        $order_detail = new OrderDetail;
        $order_detail->order_id = $idOrder;
        $order_detail->seller_id = $product->user_id;
        $order_detail->product_id = $product->id;
        $order_detail->variation = $variant->variant;
        $order_detail->price = $variant->price;
        // $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
        $order_detail->shipping_type = "home_delivery";
        // $order_detail->product_referral_code = $cartItem['product_referral_code'];
        // $order_detail->shipping_cost = 0;
        $order_detail->quantity = 1;

        $order_detail->save();

        $variant->qty -= 1;
        $variant->save();

        $order->grand_total = $order->grand_total + $order_detail->price;
        $order->save();

        $response = array(
            'status' => 'success',
            'msg' =>$product,
            );

            return response()->json( $response );
    }

    public function selectSearch(Request $request)
    {
        $products = [];

        if($request->has('q')){


            $search = $request->q;
/*            $movies =Movie::select("id", "name")
                ->where('name', 'LIKE', "%$search%")
                ->get();*/

            $products = Product::where('added_by', 'admin');

            $products = $products
                         ->where('name', 'like',  "%$search%")->get();;

        }
        return response()->json($products);
    }

    public function selectProductSize(Request $request){


        //if our chosen id and products table prod_cat_id col match the get first 100 data

        //$request->id here is the id of our chosen option id


        $data = ProductStock::where('product_id',$request->id)
            ->where('qty','>',0)->get();
        // add currency symbol before price in currency
        foreach ($data as $key => $value) {
            $value->price = single_price($value->price);
        }
        return response()->json($data);//then sent this data to ajax success
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $storeOrder)
    {
        $carts = Cart::where('user_id', Auth::user()->id)
            ->get();

        if ($carts->isEmpty()) {
            flash(translate('Your cart is empty'))->warning();
            return redirect()->route('home');
        }

        if($carts[0]['pickup_point'] == null) {
            $address = Address::where('id', $carts[0]['address_id'])->first();
            //return dd($address);

            if($address->city == null) {
                flash(translate('Please select your city'))->warning();
                return redirect()->route('checkout.shipping_info');
            }

            $shippingAddress = [];
            if ($address != null) {
                $shippingAddress['name']        = Auth::user()->name;
                $shippingAddress['email']       = Auth::user()->email;
                $shippingAddress['address_type'] = $address->address_type;
                $shippingAddress['address']     = $address->address;
                $shippingAddress['state']     = $address->state->name;
                $shippingAddress['city']       = $address->city->name;
                $shippingAddress['city_id'] = $address->city->id;
                $shippingAddress['bloc']        = $address->bloc;
                $shippingAddress['avenue'] = $address->avenue;
                $shippingAddress['street'] = $address->street;
                $shippingAddress['house'] = $address->house;
                $shippingAddress['phone']       = $address->phone;
                $shippingAddress['address_label'] = $address->address_label;
                $shippingAddress['building_name'] = $address->building_name;
                $shippingAddress['building_number'] = $address->building_number;
                $shippingAddress['apt_number'] = $address->apt_number;
                $shippingAddress['floor'] = $address->floor;
                if ($address->latitude || $address->longitude) {
                    $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
                }
            }
        }else{
            $shippingAddress = null;
        }

        if($storeOrder == false) {
            $combined_order = new CombinedOrder;
            $combined_order->user_id = Auth::user()->id;
            $combined_order->shipping_address = json_encode($shippingAddress);
            $combined_order->save();
        }else{
            if($request->input('payment_option') == "cash_on_delivery") {
                $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
            }
            elseif($request->input('payment_option') == "wallet"){
                $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
            }
            else{
                $combined_order = CombinedOrder::findOrFail(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id);
            }
        }

        $seller_products = array();
        foreach ($carts as $cartItem) {
            $product_ids = array();
            $product = Product::find($cartItem['product_id']);
            if (isset($seller_products[$product->user_id])) {
                $product_ids = $seller_products[$product->user_id];
            }
            array_push($product_ids, $cartItem);
            $seller_products[$product->user_id] = $product_ids;
        }

        foreach ($seller_products as $seller_product) {
            if($storeOrder == true) {
                $order = new Order;
                $order->combined_order_id = $combined_order->id;
                $order->user_id = Auth::user()->id;
                $order->shipping_address = $combined_order->shipping_address;

                $order->additional_info = $request->additional_info;

                //======== Closed By Kiron ==========
                // $order->shipping_type = $carts[0]['shipping_type'];
                // if ($carts[0]['shipping_type'] == 'pickup_point') {
                //     $order->pickup_point_id = $cartItem['pickup_point'];
                // }
                // if ($carts[0]['shipping_type'] == 'carrier') {
                //     $order->carrier_id = $cartItem['carrier_id'];
                // }


                $order->payment_type = $request->payment_option;
                $order->delivery_viewed = '0';
                $order->payment_status_viewed = '0';
                $order->code = date('Ymd-His') . rand(10, 99);
                $order->date = strtotime('now');
                $order->shipping_company = $carts[0]['shipping_company'];
                //return dd($order);
                $order->save();
            }

            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            $coupon_discount = 0;
            $addon_amount = 0;

            if($storeOrder == true) {
                    //Order Details Storing
                    foreach ($seller_product as $cartItem) {
                        $product = Product::find($cartItem['product_id']);

                        foreach(json_decode($cartItem['addons']) as $addon) {
                            if($addon->quantity > 0) {
                                $addon_amount += $addon->price * $addon->quantity;
                            }
                        }
                        $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $tax +=  cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $coupon_discount += $cartItem['discount'];

                        $product_variation = $cartItem['variation'];

                        $product_stock = $product->stocks->where('variant', $product_variation)->first();
                        if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                            flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                            $order->delete();
                            return redirect()->route('cart')->send();
                        } elseif ($product->digital != 1) {
                            $product_stock->qty -= $cartItem['quantity'];
                            $product_stock->save();
                        }

                        $order_detail = new OrderDetail;
                        $order_detail->order_id = $order->id;
                        $order_detail->seller_id = $product->user_id;
                        $order_detail->product_id = $product->id;
                        $order_detail->variation = $product_variation;
                        $order_detail->addons = $cartItem['addons'];
                        $order_detail->price = cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $order_detail->tax = cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $order_detail->shipping_type = $cartItem['shipping_type'];
                        $order_detail->product_referral_code = $cartItem['product_referral_code'];
                        $order_detail->shipping_cost = $cartItem['shipping_cost'];

                        $shipping += $order_detail->shipping_cost;
                        //End of storing shipping cost

                        $order_detail->quantity = $cartItem['quantity'];

                        if (addon_is_activated('club_point')) {
                            $order_detail->earn_point = $product->earn_point;
                        }

                        $order_detail->save();

                        $product->num_of_sale += $cartItem['quantity'];
                        $product->save();

                        $order->seller_id = $product->user_id;
                        //======== Added By Kiron ==========
                        $order->shipping_type = $cartItem['shipping_type'];
                        if ($cartItem['shipping_type'] == 'pickup_point') {
                            $order->pickup_point_id = $cartItem['pickup_point'];
                        }
                        if ($cartItem['shipping_type'] == 'carrier') {
                            $order->carrier_id = $cartItem['carrier_id'];
                        }

                        if ($product->added_by == 'seller' && $product->user->seller != null) {
                            $seller = $product->user->seller;
                            $seller->num_of_sale += $cartItem['quantity'];
                            $seller->save();
                        }

                        if (addon_is_activated('affiliate_system')) {
                            if ($order_detail->product_referral_code) {
                                $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                                $affiliateController = new AffiliateController;
                                $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                            }
                        }
                    }

                    $order->grand_total = $subtotal + $tax + $shipping + $addon_amount;

                    foreach ($seller_product as $product) {
                        if ($product->coupon_code != null) {
                            $order->coupon_discount += $product->discount;
                            $order->grand_total -= $product->discount;

                            $coupon_usage = new CouponUsage;
                            $coupon_usage->user_id = Auth::user()->id;
                            $coupon_usage->coupon_id = Coupon::where('code', $product->coupon_code)->first()->id;
                            $coupon_usage->save();
                        }
                        // break;
                    }

                    $combined_order->grand_total = $order->grand_total;

                    $order->save();
                // if (addon_is_activated('club_point')) {
                //     (new ClubPointController)->processClubPoints($order);
                // }
                $combined_order->save();

                $request->session()->put('combined_order_id', $combined_order->id);
            }else{
                foreach ($seller_product as $cartItem) {
                    foreach(json_decode($cartItem['addons']) as $addon) {
                        if($addon->quantity > 0) {
                            $addon_amount += $addon->price * $addon->quantity;
                        }
                    }
                    $shipping += $cartItem['shipping_cost'];
                    $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                    $tax +=  cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                    $coupon_discount += $cartItem['discount'];
                }
                //return dd([$subtotal, $tax, $shipping, $addon_amount, $coupon_discount]);
                $combined_order->grand_total += ($subtotal + $tax + $shipping + $addon_amount) - $coupon_discount;
                $combined_order->save();

                $request->session()->put('combined_order_id', $combined_order->id);
            }
        }
    }


    public function guest_store(Request $request, $storeOrder)
    {
        try {
            $temp_user_id = $request->session()->get('temp_user_id');
            $carts = Cart::where('temp_user_id', $temp_user_id)
                ->get();

            if ($carts->isEmpty()) {
                flash(translate('Your cart is empty'))->warning();
                return redirect()->route('home');
            }

            if($carts[0]['pickup_point'] == null || $carts[0]['pickup_point'] == 0) {

                $address = Address::where('id', $carts[0]['address_id'])->first();

                if($address->city == null) {
                    flash(translate('Please select your city'))->warning();
                    return redirect()->route('checkout.shipping_info');
                }
            }else{
                $address = null;
            }

            $guest = Guest::where('temp_user_id', $temp_user_id)->first();
            if($carts[0]['pickup_point'] == null) {
                $shippingAddress = [];
                if ($address != null) {
                    $shippingAddress['name']        = $guest->name;
                    $shippingAddress['email']       = $guest->email;
                    $shippingAddress['address_type'] = $address->address_type;
                    $shippingAddress['address']     = $address->address;
                    $shippingAddress['state']     = $address->state->name;
                    $shippingAddress['city']       = $address->city->name;
                    $shippingAddress['city_id'] = $address->city->id;
                    $shippingAddress['bloc']        = $address->bloc;
                    $shippingAddress['avenue'] = $address->avenue;
                    $shippingAddress['street'] = $address->street;
                    $shippingAddress['house'] = $address->house;
                    $shippingAddress['phone']       = $address->phone;
                    $shippingAddress['address_label'] = $address->address_label;
                    $shippingAddress['building_name'] = $address->building_name;
                    $shippingAddress['building_number'] = $address->building_number;
                    $shippingAddress['apt_number'] = $address->apt_number;
                    $shippingAddress['floor'] = $address->floor;
                    if ($address->latitude || $address->longitude) {
                        $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
                    }
                }
            }else{
                $shippingAddress = null;
            }
            // return dd($request->input('payment_option'));
            if(!$storeOrder) {
                $combined_order = new CombinedOrder;
                $combined_order->guest_id = $guest->id;
                $combined_order->shipping_address = json_encode($shippingAddress);
                $combined_order->save();
            }else{
                if($request->input('payment_option') == "cash_on_delivery") {
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                }elseif($request->input('payment_option') == null){
                    $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                }else{
                    $combined_order = CombinedOrder::findOrFail(isset($request->merchantTxnId) ? $request->merchantTxnId : $request->requested_order_id);
                }
            }

            $seller_products = array();
            foreach ($carts as $cartItem) {
                $product_ids = array();
                $product = Product::find($cartItem['product_id']);
                if (isset($seller_products[$product->user_id])) {
                    $product_ids = $seller_products[$product->user_id];
                }
                array_push($product_ids, $cartItem);
                $seller_products[$product->user_id] = $product_ids;
            }

            foreach ($seller_products as $seller_product) {
                if($storeOrder) {
                    $order = new Order;
                    $order->combined_order_id = $combined_order->id;
                    $order->guest_id = Guest::where('temp_user_id', $temp_user_id)->first()->id;
                    $order->shipping_address = $combined_order->shipping_address;

                    $order->additional_info = $request->additional_info;

                    //======== Closed By Kiron ==========
                    // $order->shipping_type = $carts[0]['shipping_type'];
                    // if ($carts[0]['shipping_type'] == 'pickup_point') {
                    //     $order->pickup_point_id = $cartItem['pickup_point'];
                    // }
                    // if ($carts[0]['shipping_type'] == 'carrier') {
                    //     $order->carrier_id = $cartItem['carrier_id'];
                    // }


                    $order->payment_type = $request->payment_option;
                    $order->delivery_viewed = '0';
                    $order->payment_status_viewed = '0';
                    $order->code = date('Ymd-His') . rand(10, 99);
                    $order->date = strtotime('now');
                    $order->shipping_company = $carts[0]['shipping_company'];
                    //return dd($order);
                    $order->save();
                }

                $subtotal = 0;
                $tax = 0;
                $shipping = 0;
                $coupon_discount = 0;
                $addon_amount = 0;

                //Order Details Storing
                if($storeOrder == true) {
                    foreach ($seller_product as $cartItem) {
                        $product = Product::find($cartItem['product_id']);

                        foreach(json_decode($cartItem['addons']) as $addon) {
                            if($addon->quantity > 0) {
                                $addon_amount += $addon->price * $addon->quantity;
                            }
                        }
                        $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $tax +=  cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $coupon_discount += $cartItem['discount'];

                        $product_variation = $cartItem['variation'];

                        $product_stock = $product->stocks->where('variant', $product_variation)->first();
                        if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                            flash(translate('The requested quantity is not available for ') . $product->getTranslation('name'))->warning();
                            $order->delete();
                            return redirect()->route('cart')->send();
                        } elseif ($product->digital != 1) {
                            $product_stock->qty -= $cartItem['quantity'];
                            $product_stock->save();
                        }

                        $order_detail = new OrderDetail;
                        $order_detail->order_id = $order->id;
                        $order_detail->seller_id = $product->user_id;
                        $order_detail->product_id = $product->id;
                        $order_detail->variation = $product_variation;
                        $order_detail->addons = $cartItem['addons'];
                        $order_detail->price = cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $order_detail->tax = cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $order_detail->shipping_type = $cartItem['shipping_type'];
                        $order_detail->product_referral_code = $cartItem['product_referral_code'];
                        $order_detail->shipping_cost = $cartItem['shipping_cost'];
                        // $order_detail->discount = $cartItem['discount'];

                        $shipping += $order_detail->shipping_cost;
                        //End of storing shipping cost

                        $order_detail->quantity = $cartItem['quantity'];

                        if (addon_is_activated('club_point')) {
                            $order_detail->earn_point = $product->earn_point;
                        }

                        $order_detail->save();

                        $product->num_of_sale += $cartItem['quantity'];
                        $product->save();

                        $order->seller_id = $product->user_id;
                        //======== Added By Kiron ==========
                        $order->shipping_type = $cartItem['shipping_type'];
                        if ($cartItem['shipping_type'] == 'pickup_point') {
                            $order->pickup_point_id = $cartItem['pickup_point'];
                        }
                        if ($cartItem['shipping_type'] == 'carrier') {
                            $order->carrier_id = $cartItem['carrier_id'];
                        }

                        if ($product->added_by == 'seller' && $product->user->seller != null) {
                            $seller = $product->user->seller;
                            $seller->num_of_sale += $cartItem['quantity'];
                            $seller->save();
                        }

                        if (addon_is_activated('affiliate_system')) {
                            if ($order_detail->product_referral_code) {
                                $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                                $affiliateController = new AffiliateController;
                                $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                            }
                        }
                    }
                    $order->grand_total = $subtotal + $tax + $shipping + $addon_amount;

                    foreach ($seller_product as $product) {
                        if ($product->coupon_code != null) {
                            $order->coupon_discount += $product->discount;
                            $order->grand_total -= $product->discount;

                            $coupon_usage = new CouponUsage;
                            $coupon_usage->user_id = Auth::user() != null ? Auth::user()->id : 0;
                            $coupon_usage->coupon_id = Coupon::where('code', $product->coupon_code)->first()->id;
                            $coupon_usage->save();
                        }
                    }
                        // break;
                    if ($seller_product[0]->coupon_code != null) {
                        $order->coupon_discount = $coupon_discount;
                        $order->grand_total -= $coupon_discount;

                        $coupon_usage = new CouponUsage;
                        $coupon_usage->guest_id = $temp_user_id;
                        $coupon_usage->coupon_id = Coupon::where('code', $seller_product[0]->coupon_code)->first()->id;
                        $coupon_usage->save();
                    }

                    $combined_order->grand_total = $order->grand_total;

                    $order->save();
                    //$combined_order->save();

                    $request->session()->put('combined_order_id', $combined_order->id);
                    $request->session()->put('order_id', $order->id);
                }else{
                    foreach ($seller_product as $cartItem) {
                        foreach(json_decode($cartItem['addons']) as $addon) {
                            if($addon->quantity > 0) {
                                $addon_amount += $addon->price * $addon->quantity;
                            }
                        }
                        $shipping += $cartItem['shipping_cost'];
                        $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
                        $tax +=  cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
                        $coupon_discount += $cartItem['discount'];
                    }
                    $combined_order->grand_total += ($subtotal + $tax + $shipping + $addon_amount) - $coupon_discount;
                    $combined_order->save();

                    $request->session()->put('combined_order_id', $combined_order->id);
                }
            }
            \Log::alert("Guest store done");
        }catch(Exception $e) {
            \Log::alert($e);
            // return dd($e);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order != null) {
            foreach ($order->orderDetails as $key => $orderDetail) {
                try {

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)->where('variant', $orderDetail->variation)->first();
                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                } catch (\Exception $e) {
                }

                $orderDetail->delete();
            }
            $order->delete();
            flash(translate('Order has been deleted successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }

    public function bulk_order_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $order_id) {
                $this->destroy($order_id);
            }
        }

        return 1;
    }

    public function order_details(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->save();
        return view('seller.order_details_seller', compact('order'));
    }

    public function update_delivery_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        if ($request->status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User::where('id', $order->user_id)->first();
            $user->balance += $order->grand_total;
            $user->save();
        }

        if (
            $order->payment_status == 'paid' ||
            $order->delivery_status == 'delivered'
        ) {
            if (addon_is_activated('club_point')) {
                if ($order->user != null) {
                    (new ClubPointController)->processClubPoints($order);
                }
            }
        }

        if($order->payment_status == 'paid' &&
        $order->delivery_status == 'delivered' &&
        $order->commission_calculated == 0) {
            calculateCommissionAffilationClubPoint($order);
        }

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {

                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    $variant = $orderDetail->variation;
                    if ($orderDetail->variation == null) {
                        $variant = '';
                    }

                    $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                        ->where('variant', $variant)
                        ->first();

                    if ($product_stock != null) {
                        $product_stock->qty += $orderDetail->quantity;
                        $product_stock->save();
                    }
                }

                if (addon_is_activated('affiliate_system')) {
                    if (($request->status == 'delivered' || $request->status == 'cancelled') &&
                        $orderDetail->product_referral_code
                    ) {

                        $no_of_delivered = 0;
                        $no_of_canceled = 0;

                        if ($request->status == 'delivered') {
                            $no_of_delivered = $orderDetail->quantity;
                        }
                        if ($request->status == 'cancelled') {
                            $no_of_canceled = $orderDetail->quantity;
                        }

                        $referred_by_user = User::where('referral_code', $orderDetail->product_referral_code)->first();

                        $affiliateController = new AffiliateController;
                        $affiliateController->processAffiliateStats($referred_by_user->id, 0, 0, $no_of_delivered, $no_of_canceled);
                    }
                }
            }
        }
        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'delivery_status_change')->first()->status == 1) {
            try {
                SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {
            }
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->delivery_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('delivery_boy')) {
            if (Auth::user()->user_type == 'delivery_boy') {
                $deliveryBoyController = new DeliveryBoyController;
                $deliveryBoyController->store_delivery_history($order);
            }
        }

        return 1;
    }

    public function update_tracking_code(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->tracking_code = $request->tracking_code;
        $order->save();

        return 1;
    }

    public function update_payment_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $order->save();


        if (
            $order->payment_status == 'paid' &&
            $order->delivery_status == 'delivered' &&
            $order->commission_calculated == 0
        ) {
            \Log::alert('commission_calculated line 977');
            calculateCommissionAffilationClubPoint($order);
        }

        //sends Notifications to user
        NotificationUtility::sendNotification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->payment_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            NotificationUtility::sendFirebaseNotification($request);
        }


        if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'payment_status_change')->first()->status == 1) {
            try {
                SmsUtility::payment_status_change(json_decode($order->shipping_address)->phone, $order);
            } catch (\Exception $e) {
            }
        }
        return 1;
    }

    public function assign_delivery_boy(Request $request)
    {
        if (addon_is_activated('delivery_boy')) {

            $order = Order::findOrFail($request->order_id);
            $order->assign_delivery_boy = $request->delivery_boy;
            $order->delivery_history_date = date("Y-m-d H:i:s");
            $order->save();

            $delivery_history = \App\Models\DeliveryHistory::where('order_id', $order->id)
                ->where('delivery_status', $order->delivery_status)
                ->first();

            if (empty($delivery_history)) {
                $delivery_history = new \App\Models\DeliveryHistory;

                $delivery_history->order_id = $order->id;
                $delivery_history->delivery_status = $order->delivery_status;
                $delivery_history->payment_type = $order->payment_type;
            }
            $delivery_history->delivery_boy_id = $request->delivery_boy;

            $delivery_history->save();

            if (env('MAIL_USERNAME') != null && get_setting('delivery_boy_mail_notification') == '1') {
                $array['view'] = 'emails.invoice';
                $array['subject'] = translate('You are assigned to delivery an order. Order code') . ' - ' . $order->code;
                $array['from'] = env('MAIL_FROM_ADDRESS');
                $array['order'] = $order;

                try {
                    Mail::to($order->delivery_boy->email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {
                }
            }

            if (addon_is_activated('otp_system') && SmsTemplate::where('identifier', 'assign_delivery_boy')->first()->status == 1) {
                try {
                    SmsUtility::assign_delivery_boy($order->delivery_boy->phone, $order->code);
                } catch (\Exception $e) {
                }
            }
        }

        return 1;
    }

    public function update_shipping_company(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->shipping_company = $request->shipping_company;
        $order->save();

        $address = json_decode($order->shipping_address);
                    //create shippment request with shipment company
                    if($request->shipping_company == 'mashkor')
                    {
                        $branch_id = rand(10000000, 99999999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(10000000, 99999999);
                        $data = [
                            'customer_name' => User::findOrFail($order->user_id)->name,
                            'mobile_number' => $address->phone,
                            'customer_address' => $address->address,
                            'customer_city' => $address->city->name,
                            'customer_area' => $address->state->name,
                            'payment_method' => $request->payment_option == 'cash_on_delivery' ? 1 : 2,
                            'amount_to_collect' => $order->grand_total,
                            'block' => $address->bloc,
                            'street' => $address->street,
                            'avenue' => $address->avenue,
                            'building' => $address->house,
                            //random id with format ********-****-****-****-************
                            'branch_id' => $branch_id,
                            'vendor_order_id' => $order->id,
                        ];
                        //return dd($data);
                        $shippment = new ShippmentController();
                        $response = $shippment->create_mashkor_order($data);
                        $order->traking_order_id = $response->data->order_number;
                        $order->branch_id = $branch_id;
                    }else if($request->shipping_company == "quick")
                    {
                        $data = [
                            'customer_name' => User::findOrFail($order->user_id)->name,
                            'recipient_email' => User::findOrFail($order->user_id)->email,
                            'mobile_number' => $address->phone,
                            'customer_address' => $address->address,
                            'customer_city' => $address->city->name,
                            'customer_area' => $address->state->name,
                            //'customer_postal_code' => $address->postal_code,
                            'payment_method' => $request->payment_option == 'cash_on_delivery' ? 1 : 2,
                            'amount_to_collect' => $order->grand_total,
                            'driver_note' => $request->additional_info,
                            'block' => $address->bloc,
                            'street' => $address->street,
                            'avenue' => $address->avenue,
                            'building' => $address->house,
                            //random id with format ********-****-****-****-************
                            'branch_id' => rand(10000000, 99999999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(10000000, 99999999),
                            'vendor_order_id' => $order->id,
                        ];
                        //return dd($data);
                        $shippment = new ShippmentController();
                        $response = $shippment->create_quick_delivery_order($data);
                        $order->traking_order_id = $response->result->orderId ?? null;
                    }else if($request->shipping_company == "armada")
                    {
                        $data = [
                            'customer_name' => User::findOrFail($order->user_id)->name,
                            'recipient_email' => User::findOrFail($order->user_id)->email,
                            'mobile_number' => $address->phone,
                            'customer_address' => $address->address,
                            'customer_city' => $address->city->name,
                            'customer_area' => $address->state->name,
                            'customer_postal_code' => $address->postal_code,
                            'payment_method' => $request->payment_option == 'cash_on_delivery' ? "cash" : "paid",
                            'amount_to_collect' => $order->grand_total,
                            'driver_note' => $request->additional_info,
                            'block' => $address->bloc,
                            'street' => $address->street,
                            'avenue' => $address->avenue,
                            'building' => $address->house,
                            //random id with format ********-****-****-****-************
                            'branch_id' => rand(10000000, 99999999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(10000000, 99999999),
                            'vendor_order_id' => $order->id,
                        ];
                        //return dd($data);
                        $shippment = new ShippmentController();
                        $response = $shippment->create_armada_delivery($data);
                        $order->traking_order_id = $response->code ?? null;
                    }
$order->save();
        return 1;
    }
}
