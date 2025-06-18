<?php

namespace App\Http\Controllers;


use App\Models\Brand;
use App\Models\Order;
use App\Models\Times;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Artisan;
use Cache;
// use CoreComponentRepository;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_dashboard(Request $request)
    {
        //CoreComponentRepository::initializeCache();
        $root_categories = Category::where('level', 0)->get();

        $cached_graph_data = Cache::remember('cached_graph_data', 86400, function() use ($root_categories){
            $num_of_sale_data = null;
            $qty_data = null;
            foreach ($root_categories as $key => $category){
                $category_ids = \App\Utility\CategoryUtility::children_ids($category->id);
                $category_ids[] = $category->id;

                $products = Product::with('stocks')->whereIn('category_id', $category_ids)->get();
                $qty = 0;
                $sale = 0;
                foreach ($products as $key => $product) {
                    $sale += $product->num_of_sale;
                    foreach ($product->stocks as $key => $stock) {
                        $qty += $stock->qty;
                    }
                }
                $qty_data .= $qty.',';
                $num_of_sale_data .= $sale.',';
            }
            $item['num_of_sale_data'] = $num_of_sale_data;
            $item['qty_data'] = $qty_data;

            return $item;
        });
        $todayOrders = 0;
        $ordersToday = Order::whereDate('created_at', \Carbon\Carbon::today())->get();
        foreach($ordersToday as $orderToday){
          $todayOrders = $todayOrders +    $orderToday->grand_total;
        }
        
        $yesterdayOrders = 0;
        $ordersYesterday = Order::whereDate('created_at', \Carbon\Carbon::yesterday())->get();
        foreach($ordersYesterday as $orderYesterday){
          $yesterdayOrders = $yesterdayOrders +    $orderYesterday->grand_total;
        }
        
        $today = Carbon::today();
        $last7Days = 0;
        $Days7last = Order::where('created_at','>', $today->subDays(7))->get();
        foreach($Days7last as $Day7last){
          $last7Days = $last7Days +   $Day7last->grand_total;
        }
        $last30Days = 0;
        $Days30last = Order::where('created_at','>', $today->subDays(30))->get();
        foreach($Days30last as $Day30last){
          $last30Days = $last30Days +   $Day30last->grand_total;
        }
        $last90Days = 0;
        $Days90last = Order::where('created_at','>', $today->subDays(90))->get();
        foreach($Days90last as $Day90last){
          $last90Days = $last90Days +   $Day90last->grand_total;
        }
        
      // return $yesterdayOrders;
        return view('backend.dashboard', compact('root_categories', 'cached_graph_data','todayOrders','yesterdayOrders','last7Days','last30Days','last90Days'));
    }
    public function filter_dashboard(Request $request)
    {
        //CoreComponentRepository::initializeCache();

        $date = explode(" / ", $request->date_range);
        $users = User::where('user_type', 'customer')->where('email_verified_at', '!=', null)
            ->whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
                Carbon::parse($date[1])->format('Y-m-d 23:59:59')])
            ->count();
        $orders = Order::whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
            Carbon::parse($date[1])->format('Y-m-d 23:59:59')])
            ->count();
        $categories =  Category::whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
            Carbon::parse($date[1])->format('Y-m-d 23:59:59')])
            ->count();
        $brands = Brand::whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
            Carbon::parse($date[1])->format('Y-m-d 23:59:59')])
            ->count();
        $products = filter_products(Product::whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
            Carbon::parse($date[1])->format('Y-m-d 23:59:59')])
        ->where('published', 1)->orderBy('num_of_sale', 'desc'))->limit(12)->get();
        $product_count = Product::whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
            Carbon::parse($date[1])->format('Y-m-d 23:59:59')])
            ->where('published', 1)->count() ;
        $product_count_admin = Product::whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
            Carbon::parse($date[1])->format('Y-m-d 23:59:59')])
            ->where('published', 1)->where('added_by', 'admin')->count() ;

        $root_categories = Category::where('level', 0)->get();
        Cache::clear();
        $cached_graph_data = Cache::remember('cached_graph_data', 86400, function() use ($root_categories,$date){
            $num_of_sale_data = null;
            $qty_data = null;
            foreach ($root_categories as $key => $category){
                $category_ids = \App\Utility\CategoryUtility::children_ids($category->id);
                $category_ids[] = $category->id;

                $products = Product::whereBetween('created_at', [Carbon::parse($date[0])->format('Y-m-d 00:00:00'),
                    Carbon::parse($date[1])->format('Y-m-d 23:59:59')])->
                with('stocks')->whereIn('category_id', $category_ids)->get();
                $qty = 0;
                $sale = 0;
                foreach ($products as $key => $product) {
                    $sale += $product->num_of_sale;
                    foreach ($product->stocks as $key => $stock) {
                        $qty += $stock->qty;
                    }
                }
                $qty_data .= $qty.',';
                $num_of_sale_data .= $sale.',';
            }
            $item['num_of_sale_data'] = $num_of_sale_data;
            $item['qty_data'] = $qty_data;

            return $item;
        });
        $todayOrders = 0;
        $ordersToday = Order::whereDate('created_at', \Carbon\Carbon::today())->get();
        foreach($ordersToday as $orderToday){
          $todayOrders = $todayOrders +    $orderToday->grand_total;
        }
        
        $yesterdayOrders = 0;
        $ordersYesterday = Order::whereDate('created_at', \Carbon\Carbon::yesterday())->get();
        foreach($ordersYesterday as $orderYesterday){
          $yesterdayOrders = $yesterdayOrders +    $orderYesterday->grand_total;
        }
        
        $today = Carbon::today();
        $last7Days = 0;
        $Days7last = Order::where('created_at','>', $today->subDays(7))->get();
        foreach($Days7last as $Day7last){
          $last7Days = $last7Days +   $Day7last->grand_total;
        }
        $last30Days = 0;
        $Days30last = Order::where('created_at','>', $today->subDays(30))->get();
        foreach($Days30last as $Day30last){
          $last30Days = $last30Days +   $Day30last->grand_total;
        }
        $last90Days = 0;
        $Days90last = Order::where('created_at','>', $today->subDays(90))->get();
        foreach($Days90last as $Day90last){
          $last90Days = $last90Days +   $Day90last->grand_total;
        }
        
        return view('backend.dashboard_filter', compact('root_categories', 'cached_graph_data',
            'users','orders','categories','brands','products','product_count','product_count_admin','date','todayOrders','yesterdayOrders','last7Days','last30Days','last90Days'));
    }
    public function worktimes(){
        $times = Times::first();
        return view('backend.work_times.worktimes',compact('times'));
    }
    public function worktimesupdate(Request $request){
      //  return $request;
        $times =  Times::firstOrNew();
        $times->friday = $request->friday;
        $times->saturday = $request->saturday;
        $times->sunday = $request->sunday;
        $times->monday = $request->monday;
        $times->tuesday = $request->tuesday;
        $times->wednesday = $request->wednesday;
        $times->thursday = $request->thursday;
        $times->save();
        return back();
    }
    function clearCache(Request $request)
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        flash(translate('Cache cleared successfully'))->success();
        return back();
    }

    function get_products_by_category_brand(Request $request)
    {
        // $products = filter_products(Product::where('category_id', $request->category_id)->where('brand_id', $request->brand_id))->get();
        $products = new Product;
        if ($request->category_ids) {
            $products = $products->whereIn('category_id', $request->category_ids);
        }
        if ($request->brand_ids) {
            $products = $products->whereIn('brand_id', $request->brand_ids);
        }
        $products = $products->get();
        $products = filter_products($products);
        return response()->json(['products' => $products]);
    }
}
