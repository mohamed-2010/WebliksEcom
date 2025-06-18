<?php

namespace App\Http\Controllers;

use App\Models\BrancheUser;
use App\Models\SalesExport;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\CommissionHistory;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\User;
use App\Models\Search;
use App\Models\Shop;
use Illuminate\Support\Facades\Route;
use Auth;

class ReportController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:in_house_product_sale_report'])->only('in_house_sale_report');
        $this->middleware(['permission:seller_products_sale_report'])->only('seller_sale_report');
        $this->middleware(['permission:products_stock_report'])->only('stock_report');
        $this->middleware(['permission:product_wishlist_report'])->only('wish_report');
        $this->middleware(['permission:user_search_report'])->only('user_search_report');
        $this->middleware(['permission:commission_history_report'])->only('commission_history');
        $this->middleware(['permission:wallet_transaction_report'])->only('wallet_transaction_history');
        $this->middleware(['permission:sales_report'])->only('sales_report');
    }

    public function stock_report(Request $request)
    {
        $sort_by =null;
        $products = Product::orderBy('created_at', 'desc');
        if ($request->has('category_id')){
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(15);
        return view('backend.reports.stock_report', compact('products','sort_by'));
    }

    public function in_house_sale_report(Request $request)
    {
        $sort_by =null;
        $products = Product::orderBy('num_of_sale', 'desc')->where('added_by', 'admin');
        if ($request->has('category_id')){
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(15);
        return view('backend.reports.in_house_sale_report', compact('products','sort_by'));
    }

    public function seller_sale_report(Request $request)
    {
        $sort_by =null;
        // $sellers = User::where('user_type', 'seller')->orderBy('created_at', 'desc');
        $sellers = Shop::with('user')->orderBy('created_at', 'desc');
        if ($request->has('verification_status')){
            $sort_by = $request->verification_status;
            $sellers = $sellers->where('verification_status', $sort_by);
        }
        $sellers = $sellers->paginate(10);
        return view('backend.reports.seller_sale_report', compact('sellers','sort_by'));
    }

    public function wish_report(Request $request)
    {
        $sort_by =null;
        $products = Product::orderBy('created_at', 'desc');
        if ($request->has('category_id')){
            $sort_by = $request->category_id;
            $products = $products->where('category_id', $sort_by);
        }
        $products = $products->paginate(10);
        return view('backend.reports.wish_report', compact('products','sort_by'));
    }

    public function user_search_report(Request $request){
        $searches = Search::orderBy('count', 'desc')->paginate(10);
        return view('backend.reports.user_search_report', compact('searches'));
    }

    public function commission_history(Request $request) {
        $seller_id = null;
        $date_range = null;

        if(Auth::user()->user_type == 'seller') {
            $seller_id = Auth::user()->id;
        } if($request->seller_id) {
            $seller_id = $request->seller_id;
        }

        $commission_history = CommissionHistory::orderBy('created_at', 'desc');

        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $commission_history = $commission_history->where('created_at', '>=', $date_range1[0]);
            $commission_history = $commission_history->where('created_at', '<=', $date_range1[1]);
        }
        if ($seller_id){

            $commission_history = $commission_history->where('seller_id', '=', $seller_id);
        }

        $commission_history = $commission_history->paginate(10);
        if(Auth::user()->user_type == 'seller') {
            return view('seller.reports.commission_history_report', compact('commission_history', 'seller_id', 'date_range'));
        }
        return view('backend.reports.commission_history_report', compact('commission_history', 'seller_id', 'date_range'));
    }

    public function wallet_transaction_history(Request $request) {
        $user_id = null;
        $date_range = null;

        if($request->user_id) {
            $user_id = $request->user_id;
        }

        $users_with_wallet = User::whereIn('id', function($query) {
            $query->select('user_id')->from(with(new Wallet)->getTable());
        })->get();

        $wallet_history = Wallet::orderBy('created_at', 'desc');

        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $wallet_history = $wallet_history->where('created_at', '>=', $date_range1[0]);
            $wallet_history = $wallet_history->where('created_at', '<=', $date_range1[1]);
        }
        if ($user_id){
            $wallet_history = $wallet_history->where('user_id', '=', $user_id);
        }

        $wallets = $wallet_history->paginate(10);

        return view('backend.reports.wallet_history_report', compact('wallets', 'users_with_wallet', 'user_id', 'date_range'));
    }

    public function salesReport(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';
        $date_range = null;


        $orders = Order::orderBy('id', 'desc');
        // $admin_user_id = User::where('user_type', 'admin')->first()->id;

        // if (
        //     Route::currentRouteName() == 'inhouse_orders.index' &&
        //     Auth::user()->can('view_inhouse_orders')
        // ) {
        //     $orders = $orders->where('orders.seller_id', '=', $admin_user_id);
        // } else if (
        //     Route::currentRouteName() == 'seller_orders.index' &&
        //     Auth::user()->can('view_seller_orders')
        // ) {
        //     $orders = $orders->where('orders.seller_id', '!=', $admin_user_id);
        // } else if (
        //     Route::currentRouteName() == 'pick_up_point.index' &&
        //     Auth::user()->can('view_pickup_point_orders')
        // ) {
        //     $orders->where('shipping_type', 'pickup_point')->orderBy('code', 'desc');
        //     if (
        //         Auth::user()->user_type == 'staff' &&
        //         Auth::user()->staff->pick_up_point != null
        //     ) {
        //         $orders->where('shipping_type', 'pickup_point')
        //             ->where('pickup_point_id', Auth::user()->staff->pick_up_point->id);
        //     }
        // } else if (
        //     Route::currentRouteName() == 'all_orders.index' &&
        //     Auth::user()->can('view_all_orders')
        // ) {
        // } else {
        //     abort(403);
        // }

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
        // if ($date != null) {
        //     $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])) . '  00:00:00')
        //         ->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])) . '  23:59:59');
        // }
        if ($request->date_range) {
            $date_range = $request->date_range;
            $date_range1 = explode(" / ", $request->date_range);
            $orders = $orders->where('created_at', '>=', $date_range1[0]);
            $orders = $orders->where('created_at', '<=', $date_range1[1]);
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
        return view('backend.reports.salesReport', compact('orders', 'sort_search', 'payment_status', 'delivery_status', 'date'));
    }

    // generate sales report as excel
    public function salesReportExport(Request $request)
    {
        $orders = Order::orderBy('id', 'desc');

        if ($request->search) {
            $orders = $orders->where('code', 'like', '%' . $request->search . '%');
        }

        if ($request->payment_status) {
            $orders = $orders->where('payment_status', $request->payment_status);
        }

        if ($request->delivery_status) {
            $orders = $orders->where('delivery_status', $request->delivery_status);
        }

        if ($request->date_range) {
            $date_range = explode(" / ", $request->date_range);
            $orders = $orders->whereBetween('created_at', [$date_range[0], $date_range[1]]);
        }

        // Handling staff role to filter by assigned branches
        // if (Auth::user()->user_type == 'staff') {
        //     $branch_user = BrancheUser::with('branche.cities')
        //         ->where('user_id', Auth::user()->id)
        //         ->get();

        //     $cities_id = $branch_user->pluck('branche.cities.*.id')->flatten()->toArray();

        //     if (!empty($cities_id)) {
        //         $orders = $orders->where(function ($query) use ($cities_id) {
        //             foreach ($cities_id as $city_id) {
        //                 $query->orWhere('shipping_address', 'like', '%' . $city_id . '%');
        //             }
        //         });
        //     }
        // }

        $orders = $orders->get();

        $orders = $orders->map(function ($order) {
            return [
                'code' => $order->code,
                'products' => $order->orderDetails->map(function ($orderDetail) {
                    return $orderDetail->product->name ?? '';
                })->implode(', '),
                'branch' => optional($order->branch)->name,
                'customer' => optional($order->user)->name,
                'seller' => optional($order->seller)->name,
                'amount' => $order->grand_total,
                'delivery_status' => $order->delivery_status,
                'payment_method' => $order->payment_type,
                'payment_status' => $order->payment_status,
            ];
        });

        $fileName = 'sales_report_' . ($request->date_range ? str_replace(' / ', '_', $request->date_range) : 'all') . '.xlsx';

        return \Excel::download(new SalesExport($orders), $fileName);
    }

}
