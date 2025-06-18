<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\Order;
use App\Models\City;
use App\Models\User;
use App\Models\Address;
use App\Models\Addon;
use Session;
use Auth;
use Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\InvoiceEmailManager;
use App\Http\Resources\PosProductCollection;
use App\Models\BusinessSetting;
use App\Models\Country;
use App\Models\Shop;
use App\Models\State;
use App\Models\Upload;
use App\Utility\CategoryUtility;
use Illuminate\Support\Facades\Storage;

class PosController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:pos_manager'])->only('admin_index');
        $this->middleware(['permission:pos_configuration'])->only('pos_activation');
    }

    // =============== POS VIEWS ===============

    public function admin_index()
    {
        $customers = User::where('user_type', 'customer')
            ->where('email_verified_at', '!=', null)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pos.index', compact('customers'));
    }

    public function seller_index()
    {
        $customers = User::where('user_type', 'customer')
            ->where('email_verified_at', '!=', null)
            ->orderBy('created_at', 'desc')
            ->get();

        if (get_setting('pos_activation_for_seller') == 1) {
            return view('pos.frontend.seller.pos.index', compact('customers'));
        } else {
            flash(translate('POS is disable for Sellers!!!'))->error();
            return back();
        }
    }

    // =============== SEARCH PRODUCTS ===============

    public function search(Request $request)
    {
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            // For admin, we fetch products added by admin
            $products = ProductStock::join('products', 'product_stocks.product_id', '=', 'products.id')
                ->where('products.added_by', 'admin')
                ->select(
                    'products.*',
                    'product_stocks.id as stock_id',
                    'product_stocks.variant',
                    'product_stocks.price as stock_price',
                    'product_stocks.qty as stock_qty',
                    'product_stocks.image as stock_image'
                )->orderBy('products.created_at', 'desc');
        } else {
            // For seller, we fetch products belonging to that seller
            $products = ProductStock::join('products', 'product_stocks.product_id', '=', 'products.id')
                ->where('user_id', Auth::user()->id)
                ->where('published', '1')
                ->select(
                    'products.*',
                    'product_stocks.id as stock_id',
                    'product_stocks.variant',
                    'product_stocks.price as stock_price',
                    'product_stocks.qty as stock_qty',
                    'product_stocks.image as stock_image'
                )->orderBy('products.created_at', 'desc');
        }

        // If category is set
        if ($request->category != null) {
            $arr = explode('-', $request->category);
            if ($arr[0] == 'category') {
                $category_ids = CategoryUtility::children_ids($arr[1]);
                $category_ids[] = $arr[1];
                $products = $products->whereIn('products.category_id', $category_ids);
            }
        }

        // If brand is set
        if ($request->brand != null) {
            $products = $products->where('products.brand_id', $request->brand);
        }

        // If keyword is set (Product name / Barcode)
        if ($request->keyword != null) {
            $products = $products->where(function ($query) use ($request) {
                $query->where('products.name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('products.barcode', $request->keyword);
            });
        }

        // Return paginated results as a Resource Collection
        $stocks = new PosProductCollection($products->paginate(16));
        $stocks->appends([
            'keyword' => $request->keyword,
            'category' => $request->category,
            'brand' => $request->brand
        ]);
        return $stocks;
    }

    // =============== SHOW ADDON MODAL (AJAX) ===============
    //
    // This is a new method that returns the Blade partial for the addon modal.
    // You might reference it from your routes as well:
    // Route::post('pos/show_addon_modal', [PosController::class, 'show_addon_modal'])->name('pos.show_addon_modal');

    public function show_addon_modal(Request $request)
    {
        $stock = ProductStock::findOrFail($request->stock_id);
        $product = $stock->product;

        // We’ll return a partial that has all the addon selection UI
        return view('pos.partials.addon_modal', compact('product', 'stock'));
    }

    // =============== ADD TO CART (UPDATED FOR ADDONS) ===============

    public function addToCart(Request $request)
    {
        $stock = ProductStock::find($request->stock_id);
        if (!$stock) {
            return [
                'success' => 0,
                'message' => translate('Invalid stock.'),
                'view' => view('pos.cart')->render()
            ];
        }
        $product = $stock->product;

        // Basic data
        $data = [];
        $data['stock_id'] = $request->stock_id;
        $data['id'] = $product->id;
        $data['variant'] = $stock->variant;
        $data['quantity'] = $product->min_qty;

        // Stock check
        if ($stock->qty < $product->min_qty) {
            return [
                'success' => 0,
                'message' => translate("This product doesn't have enough stock for the minimum purchase quantity ") . $product->min_qty,
                'view' => view('pos.cart')->render()
            ];
        }

        // Base Price
        $price = $stock->price;
        $tax = 0;

        // Discount logic
        $discount_applicable = false;
        if (
            $product->discount_start_date == null ||
            (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date)
        ) {
            $discount_applicable = true;
        }
        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        // Tax logic
        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        // =============== ADDONS LOGIC ===============
        $addons = $request->addons; // Expecting array of [{id, price, quantity}, ...]
        $addon_total = 0;
        $addon_list = [];

        if (!empty($addons) && is_array($addons)) {
            foreach ($addons as $addon) {
                $addon_id = $addon['id'];
                $addon_qty = $addon['quantity'];
                $addon_price = floatval($addon['price']);

                if ($addon_qty > 0) {
                    $addon_total += ($addon_price * $addon_qty);

                    // If you want to retrieve the name from the DB:
                    $category_addon = \App\Models\CategoryAddon::find($addon_id);

                    $addon_list[] = [
                        'id' => $addon_id,
                        'name' => $category_addon ? $category_addon->getTranslation('name') : '',
                        'price' => $addon_price,
                        'quantity' => $addon_qty,
                    ];
                }
            }
        }

        // Incorporate addon cost into final price
        $price_with_addons = $price + $addon_total;

        // Store in session
        $data['price'] = $price;
        $data['tax'] = $tax;
        $data['addons'] = $addon_list;  // keep the addon details

        // =============== PUT IN SESSION ===============
        if ($request->session()->has('pos.cart')) {
            $cart = collect($request->session()->get('pos.cart'));
            $foundInCart = false;

            // Attempt to find same product+variant+addon combo
            $cart = $cart->map(function ($cartItem, $key) use ($data, $stock, &$foundInCart) {
                if (
                    $cartItem['id'] == $data['id'] &&
                    $cartItem['stock_id'] == $data['stock_id'] &&
                    json_encode($cartItem['addons']) == json_encode($data['addons'])
                ) {
                    // same product, variant, and same addon set
                    $foundInCart = true;
                    if ($stock->qty >= ($cartItem['quantity'] + 1)) {
                        $cartItem['quantity'] += 1;
                    } else {
                        // not enough stock
                    }
                }
                return $cartItem;
            });

            // If not found, push a new entry
            if (!$foundInCart) {
                $cart->push($data);
            }
            $request->session()->put('pos.cart', $cart);
        } else {
            // cart doesn’t exist, create new
            $cart = collect([$data]);
            $request->session()->put('pos.cart', $cart);
        }

        return [
            'success' => 1,
            'message' => '',
            'view' => view('pos.cart')->render()
        ];
    }

    // =============== UPDATE QUANTITY ===============

    public function updateQuantity(Request $request)
    {
        $cart = $request->session()->get('pos.cart', collect([]));
        $cart = $cart->map(function ($object, $key) use ($request) {
            if ($key == $request->key) {
                $product = Product::find($object['id']);
                $product_stock = $product->stocks->where('id', $object['stock_id'])->first();

                if ($product_stock->qty >= $request->quantity) {
                    $object['quantity'] = $request->quantity;
                } else {
                    return [
                        'success' => 0,
                        'message' => translate("This product doesn't have more stock."),
                        'view' => view('pos.cart')->render()
                    ];
                }
            }
            return $object;
        });
        $request->session()->put('pos.cart', $cart);

        return [
            'success' => 1,
            'message' => '',
            'view' => view('pos.cart')->render()
        ];
    }

    // =============== REMOVE FROM CART ===============

    public function removeFromCart(Request $request)
    {
        if (Session::has('pos.cart')) {
            $cart = Session::get('pos.cart', collect([]));
            $cart->forget($request->key);
            Session::put('pos.cart', $cart);

            $request->session()->put('pos.cart', $cart);
        }

        return view('pos.cart');
    }

    // =============== SHIPPING ADDRESS VIEWS ===============

    public function getShippingAddress(Request $request)
    {
        $user_id = $request->id;
        Session::put('pos.user_id', $user_id);

        $typeOfAddress = 'online';

        if ($user_id == 'online') {
            $typeOfAddress = 'guest';
            return view('pos.guest_shipping_address', compact('typeOfAddress'));
        } else if ($user_id == '') {
            $typeOfAddress = 'walk_in_customer';
            return view('pos.walk_in_customer', compact('typeOfAddress'));
        } else {
            $typeOfAddress = 'user';
            return view('pos.shipping_address', compact('user_id', 'typeOfAddress'));
        }
    }

    public function set_shipping_address(Request $request)
    {
        if ($request->address_id != null) {
            $address = Address::findOrFail($request->address_id);
            $guest = $address->user;
            $shippingAddress['name'] = $guest->name;
            $shippingAddress['email'] = $guest->email;
            $shippingAddress['address_type'] = $address->address_type;
            $shippingAddress['address'] = $address->address;
            $shippingAddress['state'] = $address->state->name;
            $shippingAddress['city'] = $address->city->name;
            $shippingAddress['city_id'] = $address->city->id;
            $shippingAddress['bloc'] = $address->bloc;
            $shippingAddress['avenue'] = $address->avenue;
            $shippingAddress['street'] = $address->street;
            $shippingAddress['house'] = $address->house;
            $shippingAddress['phone'] = $address->phone;
            $shippingAddress['address_label'] = $address->address_label;
            $shippingAddress['building_name'] = $address->building_name;
            $shippingAddress['building_number'] = $address->building_number;
            $shippingAddress['apt_number'] = $address->apt_number;
            $shippingAddress['floor'] = $address->floor;
            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }elseif($request->selected_type){
            $shippingAddress['name'] = $request->address_title ?? '';
            $shippingAddress['email'] = $request->email ?? '';
            $shippingAddress['address_type'] = $request->selected_type ?? '';
            $shippingAddress['address'] = $request->address ?? '';
            $shippingAddress['state'] = $request->province ?? '';
            $shippingAddress['city'] = $request->cities != null ? City::find($request->cities)->name : '';
            $shippingAddress['city_id'] = $request->cities ?? '';
            $shippingAddress['bloc'] = $request->bloc ?? '';
            $shippingAddress['avenue'] = $request->avenue ?? '';
            $shippingAddress['street'] = $request->street ?? '';
            $shippingAddress['house'] = $request->house_number ?? '';
            $shippingAddress['office_number'] = $request->office_number ?? '';
            $shippingAddress['phone'] = $request->phone ?? '';
            $shippingAddress['address_label'] = $request->address_label ?? '';
            $shippingAddress['building_name'] = $request->apartment_building ?? '';
            $shippingAddress['building_number'] = $request->apartment_building ?? '';
            $shippingAddress['apt_number'] = $request->apartment_apt_number ?? '';
            $shippingAddress['floor'] = $request->apartment_floor ?? '';
            if ($request->latitude || $request->longitude) {
                $shippingAddress['lat_lang'] = $request->latitude . ',' . $request->longitude;
            }
        }else {
            // from request
            $shippingAddress['name'] = $request->name ?? '';
            $shippingAddress['email'] = $request->email ?? '';
            $shippingAddress['address_type'] = $request->address_type ?? '';
            $shippingAddress['address'] = $request->address ?? '';
            $shippingAddress['state'] = $request->state_id != null ? State::find($request->state_id)->name : '';
            $shippingAddress['city'] = $request->city_id != null ? City::find($request->city_id)->name : '';
            $shippingAddress['city_id'] = $request->city_id ?? '';
            $shippingAddress['bloc'] = $request->bloc ?? '';
            $shippingAddress['avenue'] = $request->avenue ?? '';
            $shippingAddress['street'] = $request->street ?? '';
            $shippingAddress['house'] = $request->house ?? '';
            $shippingAddress['phone'] = $request->phone ?? '';
            $shippingAddress['address_label'] = $request->address_label ?? '';
            $shippingAddress['building_name'] = $request->building_name ?? '';
            $shippingAddress['building_number'] = $request->building_number ?? '';
            $shippingAddress['apt_number'] = $request->apt_number ?? '';
            $shippingAddress['floor'] = $request->floor ?? '';
            if ($request->latitude || $request->longitude) {
                $shippingAddress['lat_lang'] = $request->latitude . ',' . $request->longitude;
            }
        }

        $shipping_info = $shippingAddress;
        $request->session()->put('pos.shipping_info', $shipping_info);
    }

    // =============== SET DISCOUNT / SHIPPING ===============

    public function setDiscount(Request $request)
    {
        if ($request->discount >= 0 && $request->discount_type != null) {
            Session::put('pos.discount', $request->discount);
            Session::put('pos.discount_type', $request->discount_type);
        }
        return view('pos.cart');
    }

    public function setShipping(Request $request)
    {
        if ($request->shipping != null) {
            Session::put('pos.shipping', $request->shipping);
        }
        return view('pos.cart');
    }

    // =============== ORDER SUMMARY & PLACE ORDER ===============

    public function get_order_summary(Request $request)
    {
        // dd(Session::get('pos.cart'));
        return view('pos.order_summary');
    }

    public function order_store(Request $request)
    {
        if (Session::get('pos.shipping_info') == null && Session::get('pos.user_id') != "") {
            return ['success' => 0, 'message' => translate("Please Add Shipping Information.")];
        }

        if (Session::has('pos.cart') && count(Session::get('pos.cart')) > 0) {
            $order = new Order;

            $shipping_info = Session::get('pos.shipping_info');
            if ($request->user_id == null) {
                $order->guest_id = mt_rand(100000, 999999);
            } else {
                $order->user_id = $request->user_id;
            }
            // shipping
            $data['name'] = $shipping_info['name'] ?? '';
            $data['email'] = $shipping_info['email'] ?? '';
            $data['address_type'] = $shipping_info['address_type'] ?? '';
            $data['address'] = $shipping_info['address'] ?? '';
            $data['state'] = $shipping_info['state'] ?? '';
            $data['city'] = $shipping_info['city'] ?? '';
            $data['city_id'] = $shipping_info['city_id'] ?? '';
            $data['bloc'] = $shipping_info['bloc'] ?? '';
            $data['avenue'] = $shipping_info['avenue'] ?? '';
            $data['street'] = $shipping_info['street'] ?? '';
            $data['house'] = $shipping_info['house'] ?? '';
            $data['phone'] = $shipping_info['phone'] ?? '';
            $data['address_label'] = $shipping_info['address_label'] ?? '';
            $data['building_name'] = $shipping_info['building_name'] ?? '';
            $data['building_number'] = $shipping_info['building_number'] ?? '';
            $data['apt_number'] = $shipping_info['apt_number'] ?? '';
            $data['office_number'] = $shipping_info['office_number'] ?? '';
            $data['floor'] = $shipping_info['floor'] ?? '';

            if (isset($shipping_info['lat_lang'])) {
                $data['lat_lang'] = $shipping_info['lat_lang'];
            }
            $order->shipping_address = json_encode($data);
            $order->shipping_type = "pos";

            $order->payment_type = $request->payment_type;
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->code = date('Ymd-His') . rand(10, 99);
            $order->date = strtotime('now');
            $order->payment_status = $request->payment_type != 'cash_on_delivery' ? 'paid' : 'unpaid';
            $order->payment_details = $request->payment_type;

            if ($request->payment_type == 'offline_payment') {
                if ($request->offline_trx_id == null) {
                    return [
                        'success' => 0,
                        'message' => translate("Transaction ID can not be null.")
                    ];
                }
                $datax['name'] = $request->offline_payment_method;
                $datax['amount'] = $request->offline_payment_amount;
                $datax['trx_id'] = $request->offline_trx_id;
                // $datax['photo']  = $request->offline_payment_proof; // if needed
                $order->manual_payment_data = json_encode($datax);
                $order->manual_payment = 1;
            }

            if ($order->save()) {
                $subtotal = 0;
                $tax = 0;
                foreach (Session::get('pos.cart') as $key => $cartItem) {
                    $product_stock = ProductStock::find($cartItem['stock_id']);
                    $product = $product_stock->product;
                    $product_variation = $product_stock->variant;

                    $subtotal += $cartItem['price'] * $cartItem['quantity'];
                    foreach($cartItem['addons'] as $addon){
                        $subtotal += $addon['price'] * $addon['quantity'];
                    }
                    $tax += $cartItem['tax'] * $cartItem['quantity'];

                    // check stock
                    if ($cartItem['quantity'] > $product_stock->qty) {
                        $order->delete();
                        return [
                            'success' => 0,
                            'message' => $product->name . ' (' . $product_variation . ') ' . translate(" just stock outs.")
                        ];
                    } else {
                        $product_stock->qty -= $cartItem['quantity'];
                        $product_stock->save();
                    }

                    // create order_detail
                    $order_detail = new OrderDetail;
                    $order_detail->order_id = $order->id;
                    $order_detail->seller_id = $product->user_id;
                    $order_detail->product_id = $product->id;
                    $order_detail->payment_status = $request->payment_type != 'cash_on_delivery' ? 'paid' : 'unpaid';
                    $order_detail->variation = $product_variation;
                    $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                    $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                    $order_detail->quantity = $cartItem['quantity'];
                    $order_detail->shipping_type = null;

                    if (Session::get('pos.shipping', 0) >= 0) {
                        $order_detail->shipping_cost = Session::get('pos.shipping', 0) / count(Session::get('pos.cart'));
                    } else {
                        $order_detail->shipping_cost = 0;
                    }

                    // Store the chosen addons
                    if (!empty($cartItem['addons'])) {
                        $order_detail->addons = json_encode($cartItem['addons']);
                    }

                    $order_detail->save();

                    // increase product sale
                    $product->num_of_sale++;
                    $product->save();
                }

                // final total
                $order->grand_total = $subtotal + $tax + Session::get('pos.shipping', 0);

                if (Session::has('pos.discount')) {
                    if (Session::get('pos.discount_type') == 'flat') {
                        $order->grand_total -= Session::get('pos.discount', 0);
                        $order->coupon_discount = Session::get('pos.discount');
                    } else {
                        // percentage
                        $discount_percent = Session::get('pos.discount', 0);
                        $order->grand_total -= (($subtotal + $tax) * $discount_percent) / 100;
                        $order->coupon_discount = (($subtotal + $tax) * $discount_percent) / 100;
                    }
                    
                }

                // We assume that all products in this order come from the same seller
                // but your logic may differ
                $order->seller_id = $product->user_id;
                $order->save();

                // Send emails (if you wish)...

                // If user is not guest, handle club points or affiliates...
                if ($request->user_id != NULL && $order->payment_status == 'paid') {
                    calculateCommissionAffilationClubPoint($order);
                }

                // Clear session
                Session::forget('pos.shipping_info');
                Session::forget('pos.shipping');
                Session::forget('pos.discount');
                Session::forget('pos.cart');

                return [
                    'success' => 1,
                    'message' => translate('Order Completed Successfully.'),
                    // order with items
                    'order' => Order::with('orderDetails', 'orderDetails.product')->find($order->id)
                ];
            } else {
                return [
                    'success' => 0,
                    'message' => translate('Please input customer information.')
                ];
            }
        }
        return [
            'success' => 0,
            'message' => translate("Please select a product.")
        ];
    }

    public function pos_activation(Request $request)
    {
        $sort_search = null;
        $approved = null;
        $shops = Shop::whereIn('user_id', function ($query) {
                       $query->select('id')
                       ->from(with(new User)->getTable());
                    })->latest();

        if ($request->has('search')) {
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'seller')->where(function ($user) use ($sort_search) {
                $user->where('name', 'like', '%' . $sort_search . '%')->orWhere('email', 'like', '%' . $sort_search . '%');
            })->pluck('id')->toArray();
            $shops = $shops->where(function ($shops) use ($user_ids) {
                $shops->whereIn('user_id', $user_ids);
            });
        }
        if ($request->approved_status != null) {
            $approved = $request->approved_status;
            $shops = $shops->where('verification_status', $approved);
        }
        $shops = $shops->paginate(15);
        $pos_title = get_setting('pos_title');
        $pos_image = get_setting('pos_image');

        return view('pos.pos_activation', compact('shops', 'sort_search', 'approved', 'pos_title', 'pos_image'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            // 'pos_title' => 'required|string|max:255',
            'pos_image' => 'nullable|integer',
        ]);

        // BusinessSetting::updateOrCreate(
        //     ['type' => 'pos_title'],
        //     ['value' => $request->pos_title]
        // );
        BusinessSetting::updateOrCreate(
            ['type' => 'pos_image'],
            ['value' => $request->pos_image]
        );
        Cache::flush();


        return redirect()->back()->with('success', 'POS settings updated successfully.');
    }

    public function updateAddon(Request $request)
    {
        // We expect: cartKey, addonId, quantity
        $cartKey = $request->cartKey;
        $addonId = $request->addonId;
        $quantity = intval($request->quantity);

        if (!Session::has('pos.cart')) {
            return [
                'success' => 0,
                'message' => translate('No cart found.'),
                'view' => view('pos.cart')->render()
            ];
        }

        $cart = collect(Session::get('pos.cart'));
        // We find the item with index $cartKey
        if (!$cart->has($cartKey)) {
            return [
                'success' => 0,
                'message' => translate('Invalid cart key.'),
                'view' => view('pos.cart')->render()
            ];
        }

        $item = $cart[$cartKey];

        // We'll modify the item['addons'] array
        if (!empty($item['addons'])) {
            foreach ($item['addons'] as $index => $addon) {
                if ($addon['id'] == $addonId) {
                    // Found the matching addon
                    if ($quantity <= 0) {
                        // If new quantity is 0 or less, remove it from the array
                        unset($item['addons'][$index]);
                    } else {
                        // Otherwise, update the quantity
                        $item['addons'][$index]['quantity'] = $quantity;
                    }
                }
            }
            // Re-index the array (so we don’t keep numeric gaps)
            $item['addons'] = array_values($item['addons']);
        }

        // Recalculate the item’s base price with updated addons
        $item = $this->recalculateItemPrice($item);

        // Put it back into the cart
        $cart[$cartKey] = $item;
        Session::put('pos.cart', $cart);

        // Return updated cart
        return [
            'success' => 1,
            'message' => '',
            'view' => view('pos.cart')->render()
        ];
    }

    public function removeAddon(Request $request)
    {
        // Very similar to updateAddon, but forcibly remove the addon
        $cartKey = $request->cartKey;
        $addonId = $request->addonId;

        if (!Session::has('pos.cart')) {
            return [
                'success' => 0,
                'message' => translate('No cart found.'),
                'view' => view('pos.cart')->render()
            ];
        }

        $cart = collect(Session::get('pos.cart'));
        if (!$cart->has($cartKey)) {
            return [
                'success' => 0,
                'message' => translate('Invalid cart key.'),
                'view' => view('pos.cart')->render()
            ];
        }

        $item = $cart[$cartKey];
        if (!empty($item['addons'])) {
            foreach ($item['addons'] as $index => $addon) {
                if ($addon['id'] == $addonId) {
                    // remove
                    unset($item['addons'][$index]);
                }
            }
            $item['addons'] = array_values($item['addons']);
        }

        // Recalc item price
        $item = $this->recalculateItemPrice($item);

        // Put it back
        $cart[$cartKey] = $item;
        Session::put('pos.cart', $cart);

        return [
            'success' => 1,
            'message' => '',
            'view' => view('pos.cart')->render()
        ];
    }

    private function recalculateItemPrice($item)
    {
        // $item contains: 'id', 'price', 'tax', 'addons', ...
        // But we want to recalc from the product’s base price + addons

        $stock = ProductStock::find($item['stock_id']);
        if (!$stock)
            return $item; // fallback

        $product = $stock->product;

        // 1) Start with base price
        $basePrice = $stock->price;

        // 2) Apply product discount if any
        $discount_applicable = false;
        if (
            $product->discount_start_date == null ||
            (time() >= $product->discount_start_date && time() <= $product->discount_end_date)
        ) {
            $discount_applicable = true;
        }
        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $basePrice -= ($basePrice * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $basePrice -= $product->discount;
            }
        }
        if ($basePrice < 0)
            $basePrice = 0; // safety

        // 3) Now sum up the addon totals again
        $addons_total = 0;
        if (!empty($item['addons'])) {
            foreach ($item['addons'] as $addon) {
                $addons_total += ($addon['price'] * $addon['quantity']);
            }
        }

        $price_with_addons = $basePrice + $addons_total;

        // 4) Recalc taxes
        $tax_total = 0;
        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax_total += ($basePrice * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax_total += $product_tax->tax;
            }
        }

        // Update item array
        $item['price'] = $basePrice;
        $item['tax'] = $tax_total;

        return $item;
    }

    public function getInvoiceDetails(Request $request) {
		$orderId = $request->get('order_id');
        $order = Order::with('orderDetails')->find($orderId);

		return response()->json([
			'id' => $order->id,
			'date' => $order->created_at->format('Y-m-d H:i:s'),
			'customer_name' => $order->customer->name ?? 'Walk-in Customer',
			'total' => $order->total,
            'items' => $order->orderDetails->map(function ($item) {
				return [
					'name' => $item->name,
					'quantity' => $item->quantity,
					'price' => $item->price,
					'total' => $item->quantity * $item->price,
				];
			}),
		]);
	}
}
