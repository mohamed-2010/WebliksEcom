<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Models\ClubPointDetail;
use App\Models\ClubPoint;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\Order;
use Artisan;
use Auth;
use Log;

class ClubPointController extends Controller
{
    public function configure_index()
    {
        return view('club_points.config');
    }

    public function index()
    {
        $club_points = ClubPoint::latest()->paginate(15);
        return view('club_points.index', compact('club_points'));
    }

    public function userpoint_index()
    {
        $club_points = ClubPoint::where('user_id', Auth::user()->id)->latest()->paginate(15);
        return view('club_points.frontend.index', compact('club_points'));
    }

    public function set_point()
    {
        $products = Product::latest()->paginate(15);
        return view('club_points.set_point', compact('products'));
    }

    public function set_products_point(Request $request)
    {
        $products = Product::whereBetween('unit_price', [$request->min_price, $request->max_price])->get();
        foreach ($products as $product) {
            $product->earn_point = $request->point;
            $product->save();
        }
        flash(translate('Point has been inserted successfully for ').count($products).translate(' products'))->success();
        return redirect()->route('set_product_points');
    }

    public function set_all_products_point(Request $request)
    {
        $products = Product::all();
        foreach ($products as $product) {;
            $product->earn_point = $product->unit_price * $request->point;
            $product->save();
        }
        flash(translate('Point has been inserted successfully for ').count($products).translate(' products'))->success();
        return redirect()->route('set_product_points');
    }

    //get selected categories products form
    public function get_selected_categories_products()
    {
        $categories = Category::all();
        return view('club_points.selected_categories_products', compact('categories'));
    }

    public function set_selected_categories_poroducts_point(Request $request)
    {
        // category_ids 
        $category_ids = $request->category_ids;
        $products = Product::whereIn('category_id', $category_ids)->get();
        foreach ($products as $product) {
            $product->earn_point = $request->club_point;
            $product->save();
        }
        flash(translate('Point has been inserted successfully for ').count($products).translate(' products'))->success();

        return redirect()->route('set_product_points');
    }

    //get selected brands products form
    public function get_selected_brands_products()
    {
        $brands = Brand::all();
        return view('club_points.selected_brands_products', compact('brands'));
    }

    public function set_selected_brands_poroducts_point(Request $request)
    {
        // brand_ids 
        $brand_ids = $request->brand_ids;
        $products = Product::whereIn('brand_id', $brand_ids)->get();
        foreach ($products as $product) {
            $product->earn_point = $request->club_point;
            $product->save();
        }
        flash(translate('Point has been inserted successfully for ').count($products).translate(' products'))->success();

        return redirect()->route('set_product_points');
    }

    public function set_point_edit($id)
    {
        $product = Product::findOrFail(decrypt($id));
        return view('club_points.product_point_edit', compact('product'));
    }

    public function update_product_point(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->earn_point = $request->point;
        $product->save();
        flash(translate('Point has been updated successfully'))->success();
        return redirect()->route('set_product_points');
    }

    public function convert_rate_store(Request $request)
    {
        $club_point_convert_rate = BusinessSetting::where('type', $request->type)->first();
        if ($club_point_convert_rate != null) {
            $club_point_convert_rate->value = $request->value;
        }
        else {
            $club_point_convert_rate = new BusinessSetting;
            $club_point_convert_rate->type = $request->type;
            $club_point_convert_rate->value = $request->value;
        }
        $club_point_convert_rate->save();
        
        Artisan::call('cache:clear');
        
        flash(translate('Point convert rate has been updated successfully'))->success();
        return redirect()->route('club_points.configs');
    }

    public function processClubPoints(Order $order)
    {
        $club_point = new ClubPoint;
        $club_point->user_id = $order->user_id;
        $club_point->points = 0;
        foreach ($order->orderDetails as $key => $orderDetail) {
            $total_pts = ($orderDetail->product->earn_point) * $orderDetail->quantity;
            $club_point->points += $total_pts;
        }
        $club_point->order_id = $order->id;
        $club_point->convert_status = 0;
        $club_point->save();

        foreach ($order->orderDetails as $key => $orderDetail) {
            $club_point_detail = new ClubPointDetail;
            $club_point_detail->club_point_id = $club_point->id;
            $club_point_detail->product_id = $orderDetail->product_id;
            $club_point_detail->point = ($orderDetail->product->earn_point) * $orderDetail->quantity;
            $club_point_detail->save();
        }

        // convert club points to wallet
        // if (get_setting('club_point_auto_convert_to_wallet') == 1) {
            $wallet = new Wallet;
            $wallet->user_id = $order->user_id;
            $wallet->amount = floatval($club_point->points / get_setting('club_point_convert_rate'));
            $wallet->payment_method = 'Club Point Convert';
            $wallet->payment_details = 'Club Point Convert';
            $wallet->save();
            $user = User::findOrFail($order->user_id);
            $user->balance = $user->balance + floatval($club_point->points / get_setting('club_point_convert_rate'));
            $user->save();
            $club_point->convert_status = 1;
            $club_point->save();
        // }
    }

    public function club_point_detail($id)
    {
        $club_point_details = ClubPointDetail::where('club_point_id', decrypt($id))->paginate(12);
        return view('club_points.club_point_details', compact('club_point_details'));
    }

    public function convert_point_into_wallet(Request $request)
    {
        $club_point = ClubPoint::findOrFail($request->el);
		if($club_point->convert_status == 0) {
			$wallet = new Wallet;
			$wallet->user_id = Auth::user()->id;
			$wallet->amount = floatval($club_point->points / get_setting('club_point_convert_rate'));
			$wallet->payment_method = 'Club Point Convert';
			$wallet->payment_details = 'Club Point Convert';
			$wallet->save();
			$user = Auth::user();
			$user->balance = $user->balance + floatval($club_point->points / get_setting('club_point_convert_rate'));
			$user->save();
			$club_point->convert_status = 1;
		}
		
        if ($club_point->save()) {
            return 1;
        }
        else {
            return 0;
        }
    }

    public function get_filter_form(Request $request) 
    {
        if($request->selected_type == 'category') {
            return $this->get_selected_categories_products();
        }elseif($request->selected_type == 'brand') {
            return $this->get_selected_brands_products();
        }

        return response()->json(['error' => 'Something went wrong'], 400);
    }

    public function set_points_for_selected_categories_brands(Request $request)
    {
        // Log::info($request->all());
        if($request->selected_type == 'category') {
            return $this->set_selected_categories_poroducts_point($request);
        }elseif($request->selected_type == 'brand') {
            return $this->set_selected_brands_poroducts_point($request);
        }

        return response()->json(['error' => 'Something went wrong'], 400);
    }
}
