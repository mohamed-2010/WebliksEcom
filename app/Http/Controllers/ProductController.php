<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Category;
use App\Models\ProductTax;
use App\Models\AttributeValue;
use App\Models\BulkDiscount;
use App\Models\Cart;
use Carbon\Carbon;
// use CoreComponentRepository;
use Cache;
use Str;
use App\Services\ProductService;
use App\Services\ProductTaxService;
use App\Services\ProductFlashDealService;
use App\Services\ProductStockService;
use App\Models\CategoryAddon;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Wearepixel\LaravelGoogleShoppingFeed\LaravelGoogleShoppingFeed;

class ProductController extends Controller
{
    protected $productService;
    protected $productTaxService;
    protected $productFlashDealService;
    protected $productStockService;

    public function __construct(
        ProductService $productService,
        ProductTaxService $productTaxService,
        ProductFlashDealService $productFlashDealService,
        ProductStockService $productStockService
    ) {
        $this->productService = $productService;
        $this->productTaxService = $productTaxService;
        $this->productFlashDealService = $productFlashDealService;
        $this->productStockService = $productStockService;

        // Staff Permission Check
        $this->middleware(['permission:add_new_product'])->only('create');
        $this->middleware(['permission:show_all_products'])->only('all_products');
        $this->middleware(['permission:show_in_house_products'])->only('admin_products');
        $this->middleware(['permission:show_seller_products'])->only('seller_products');
        $this->middleware(['permission:product_edit'])->only('admin_product_edit', 'seller_product_edit');
        $this->middleware(['permission:product_duplicate'])->only('duplicate');
        $this->middleware(['permission:product_delete'])->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_products(Request $request)
    {
        // CoreComponentRepository::instantiateShopRepository();

        $type = 'In House';
        $col_name = null;
        $query = null;
        $sort_search = null;

        $products = Product::where('added_by', 'admin')->where('auction_product', 0)->where('wholesale_product', 0);

        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }
        if ($request->search != null) {
            $sort_search = $request->search;
            $products = $products
                ->where('name', 'like', '%' . $sort_search . '%')
                ->orWhereHas('stocks', function ($q) use ($sort_search) {
                    $q->where('sku', 'like', '%' . $sort_search . '%');
                });
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'sort_search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function seller_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::where('added_by', 'seller')->where('auction_product', 0)->where('wholesale_product', 0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $products = $products
                ->where('name', 'like', '%' . $request->search . '%');
            $sort_search = $request->search;
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);
        $type = 'Seller';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    public function all_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::orderBy('created_at', 'desc')->where('auction_product', 0)->where('wholesale_product', 0);
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null) {
            $sort_search = $request->search;
            $products = $products
                ->where('name', 'like', '%' . $sort_search . '%')
                ->orWhereHas('stocks', function ($q) use ($sort_search) {
                    $q->where('sku', 'like', '%' . $sort_search . '%');
                });
        }
        if ($request->type != null) {
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->paginate(15);
        $type = 'All';

        return view('backend.product.products.index', compact('products', 'type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // CoreComponentRepository::initializeCache();

        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        $category_add_ons = CategoryAddon::all();

        return view('backend.product.products.create', compact('categories', 'category_add_ons'));
    }

    public function add_more_choice_option(Request $request)
    {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->get_translation('value') . '</option>';
        }

        echo json_encode($html);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        try {
            if ($request->discount_type == "percent" && $request->discount > 100) {
                flash(translate('Discount percent cannot be more than 100'))->error();
                return back();
            } elseif ($request->discount_type == "amount" && $request->discount > $request->unit_price) {
                flash(translate('Discount amount cannot be more than the product price'))->error();
                return back();
            }
            $product = $this->productService->store($request->except([
                '_token',
                'sku',
                'choice',
                'tax_id',
                'tax',
                'tax_type',
                'flash_deal_id',
                'flash_discount',
                'flash_discount_type'
            ]));
            $request->merge(['product_id' => $product->id]);

            //VAT & Tax
            if ($request->tax_id) {
                $this->productTaxService->store($request->only([
                    'tax_id',
                    'tax',
                    'tax_type',
                    'product_id'
                ]));
            }

            //Flash Deal
            $this->productFlashDealService->store($request->only([
                'flash_deal_id',
                'flash_discount',
                'flash_discount_type'
            ]), $product);

            //Product Stock
            $this->productStockService->store($request->only([
                'colors_active',
                'colors',
                'choice_no',
                'unit_price',
                'sku',
                'current_stock',
                'product_id'
            ]), $product);

            // Product Translations
            $request->merge(['lang' => env('DEFAULT_LANGUAGE')]);
            ProductTranslation::create($request->only([
                'lang',
                'name',
                'unit',
                'description',
                'product_id',
                'slug',
                'meta_title',
                'meta_description'
            ]));

            flash(translate('Product has been inserted successfully'))->success();

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return redirect()->route('products.admin');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function store_product(ProductRequest $request)
    {
        try {
            $request['category_add_on_ids'] = json_encode($request['category_add_on_ids']);
            $product = $this->productService->store($request->except([
                '_token',
                'sku',
                'choice',
                'tax_id',
                'tax',
                'tax_type',
                'flash_deal_id',
                'flash_discount',
                'flash_discount_type'
            ]));

            $request->merge(['product_id' => $product->id]);

            //VAT & Tax
            if ($request->tax_id) {
                $this->productTaxService->store($request->only([
                    'tax_id',
                    'tax',
                    'tax_type',
                    'product_id'
                ]));
            }

            //Flash Deal
            $this->productFlashDealService->store($request->only([
                'flash_deal_id',
                'flash_discount',
                'flash_discount_type'
            ]), $product);

            //Product Stock
            $this->productStockService->store($request->only([
                'colors_active',
                'colors',
                'choice_no',
                'unit_price',
                'sku',
                'current_stock',
                'product_id'
            ]), $product);

            // Product Translations
            $request->merge(['lang' => env('DEFAULT_LANGUAGE')]);
            ProductTranslation::create($request->only([
                'lang',
                'name',
                'unit',
                'description',
                'product_id',
                'slug',
                'meta_title',
                'meta_description'
            ]));

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'product_id' => $product->id,
                'message' => translate('Product has been inserted successfully')
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_product_edit(Request $request, $id)
    {
        // CoreComponentRepository::initializeCache();

        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect('admin/digitalproducts/' . $id . '/edit');
        }

        $lang = $request->lang;
        $tags = json_decode($product->tags);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function seller_product_edit(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        if ($product->digital == 1) {
            return redirect('digitalproducts/' . $id . '/edit');
        }
        $lang = $request->lang;
        $tags = json_decode($product->tags);
        // $categories = Category::all();
        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        return view('backend.product.products.edit', compact('product', 'categories', 'tags', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        if ($request->discount_type == "percent" && $request->discount > 100) {
            flash(translate('Discount percent cannot be more than 100'))->error();
            return back();
        } elseif ($request->discount_type == "amount" && $request->discount > $request->unit_price) {
            flash(translate('Discount amount cannot be more than the product price'))->error();
            return back();
        }
        //Product
        $product = $this->productService->update($request->except([
            '_token',
            'sku',
            'choice',
            'tax_id',
            'tax',
            'tax_type',
            'flash_deal_id',
            'flash_discount',
            'flash_discount_type'
        ]), $product);

        //Product Stock
        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }

        $request->merge(['product_id' => $product->id]);
        $this->productStockService->store($request->only([
            'colors_active',
            'colors',
            'choice_no',
            'unit_price',
            'sku',
            'current_stock',
            'product_id'
        ]), $product);

        //Flash Deal
        $this->productFlashDealService->store($request->only([
            'flash_deal_id',
            'flash_discount',
            'flash_discount_type'
        ]), $product);

        //VAT & Tax
        if ($request->tax_id) {
            ProductTax::where('product_id', $product->id)->delete();
            $this->productTaxService->store($request->only([
                'tax_id',
                'tax',
                'tax_type',
                'product_id'
            ]));
        }

        // Product Translations
        ProductTranslation::updateOrCreate(
            $request->only([
                'lang',
                'product_id'
            ]),
            $request->only([
                'name',
                'unit',
                'description',
                'slug',
                'meta_title',
                'meta_description'
            ])
        );

        flash(translate('Product has been updated successfully'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->product_translations()->delete();
        $product->stocks()->delete();
        $product->taxes()->delete();

        if (Product::destroy($id)) {
            Cart::where('product_id', $id)->delete();

            flash(translate('Product has been deleted successfully'))->success();

            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return back();
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function bulk_product_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $product_id) {
                $this->destroy($product_id);
            }
        }

        return 1;
    }

    /**
     * Duplicates the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request, $id)
    {
        $product = Product::find($id);

        $product_new = $product->replicate();
        $product_new->slug = $product_new->slug . '-' . Str::random(5);
        $product_new->save();

        //Product Stock
        $this->productStockService->product_duplicate_store($product->stocks, $product_new);

        //VAT & Tax
        $this->productTaxService->product_duplicate_store($product->taxes, $product_new);

        flash(translate('Product has been duplicated successfully'))->success();
        if ($request->type == 'In House')
            return redirect()->route('products.admin');
        elseif ($request->type == 'Seller')
            return redirect()->route('products.seller');
        elseif ($request->type == 'All')
            return redirect()->route('products.all');
    }

    public function get_products_by_brand(Request $request)
    {
        $products = Product::where('brand_id', $request->brand_id)->get();
        return view('partials.product_select', compact('products'));
    }

    public function updateTodaysDeal(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->todays_deal = $request->status;
        $product->save();
        Cache::forget('todays_deal_products');
        return 1;
    }

    public function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->published = $request->status;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription') && $request->status == 1) {
            $shop = $product->user->shop;
            if (
                $shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($shop->package_invalid_at), false) < 0
                || $shop->product_upload_limit <= $shop->user->products()->where('published', 1)->count()
            ) {
                return 0;
            }
        }

        $product->save();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return 1;
    }

    public function updateProductApproval(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->approved = $request->approved;

        if ($product->added_by == 'seller' && addon_is_activated('seller_subscription')) {
            $shop = $product->user->shop;
            if (
                $shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($shop->package_invalid_at), false) < 0
                || $shop->product_upload_limit <= $shop->user->products()->where('published', 1)->count()
            ) {
                return 0;
            }
        }

        $product->save();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                foreach ($request[$name] as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = makeCombinations($options);
        return view('backend.product.products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'));
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                foreach ($request[$name] as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = makeCombinations($options);
        return view('backend.product.products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product'));
    }

    public function bulkDiscountForm()
    {
        $categories = Category::where('parent_id', 0)->with('childrenCategories')->get();
        $discounts = BulkDiscount::all();
        return view('backend.marketing.bulk_discount.bulk_discount', compact('categories', 'discounts'));
    }

    public function bulkDiscountUpdate(Request $request)
    {
        $request->validate([
            // 'category_ids' => 'required|array',
            'date_range' => 'required|string',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:amount,percent',
            'club_point_discount' => 'nullable|numeric|min:0',
        ]);

        $dateRange = explode(' to ', $request->date_range);
        $startDate = Carbon::createFromFormat('d-m-Y H:i:s', $dateRange[0]);
        $endDate = Carbon::createFromFormat('d-m-Y H:i:s', $dateRange[1]);
        $products = [];
        if (isset($request->category_ids) && count($request->category_ids) > 0) {
            $products = array_merge($products, Product::whereIn('category_id', $request->category_ids)->get()->all());
        }
        if (isset($request->brand_ids) && count($request->brand_ids) > 0) {
            $products = array_merge($products, Product::whereIn('brand_id', $request->brand_ids)->get()->all());
        }

        foreach ($products as $product) {
            $product->update([
                'discount' => $request->discount,
                'discount_type' => $request->discount_type,
                'discount_start_date' => $startDate->timestamp,
                'discount_end_date' => $endDate->timestamp
            ]);

            if ($request->has('apply_to_club_point')) {
                $newEarnPoint = max(0, $product->earn_point - $request->club_point_discount);

                $product->update(['earn_point' => $newEarnPoint]);
            }
        }

        $start_date = Carbon::parse($dateRange[0]);
        $end_date = Carbon::parse($dateRange[1]);
        BulkDiscount::create([
            'category_ids' => json_encode($request->category_ids),
            'brand_ids' => json_encode($request->brand_ids),
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'date_start' => $start_date,
            'date_end' => $end_date,
        ]);

        flash(translate('Discounts have been successfully updated.'))->success();

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return redirect()->back();
    }
    public function bulkDiscountFormNewUpdate(Request $request, $id)
    {
        $request->validate([
            // 'category_ids' => 'required|array',
            'date_range' => 'required|string',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:amount,percent'
        ]);

        $dateRange = explode(' to ', $request->date_range);
        $start_date = Carbon::parse($dateRange[0]);
        $end_date = Carbon::parse($dateRange[1]);
        $products = [];
        if (isset($request->category_ids) && count($request->category_ids) > 0) {
            $p_products = Product::whereIn('category_id', $request->category_ids)->get();
            foreach ($p_products as $p_product) {
                $products[] = $p_product;
            }
        }
        if (isset($request->brand_ids) && count($request->brand_ids) > 0) {
            $b_products = Product::whereIn('brand_id', $request->brand_ids)->get();
            foreach ($b_products as $b_product) {
                $products[] = $b_product;
            }
        }

        foreach ($products as $product) {
            $product->update([
                'discount' => $request->discount,
                'discount_type' => $request->discount_type,
                'discount_start_date' => $start_date->timestamp,
                'discount_end_date' => $end_date->timestamp
            ]);
        }

        $id = (int) base64_decode($id);
        $bulk_discount = BulkDiscount::findOrFail($id);
        $bulk_discount->update([
            'category_ids' => json_encode($request->category_ids),
            'brand_ids' => json_encode($request->brand_ids),
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'date_start' => $start_date,
            'date_end' => $end_date,
        ]);

        flash(translate('Discounts have been successfully updated.'))->success();
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        return redirect()->route('products.bulk-discount-form');
    }
    public function bulkDiscountFormEdit($id)
    {
        $id = decrypt($id);
        $data['bulk_discount'] = BulkDiscount::findOrFail($id);
        $data['categories'] = Category::all();
        return view('backend.marketing.bulk_discount.bulk_discount_edit', $data);
    }

    public function bulkDiscountFormDelete($id)
    {
        $id = base64_decode($id);
        $bulk_discount = BulkDiscount::findOrFail($id);
        $cat_ids = json_decode($bulk_discount->category_ids);
        $brand_ids = json_decode($bulk_discount->brand_ids);
        $products = [];
        if ($cat_ids != null && count($cat_ids) > 0) {
            $c_products = Product::whereIn('category_id', $cat_ids)->get();
            foreach ($c_products as $c_product) {
                $products[] = $c_product;
            }
        }
        if ($brand_ids != null && count($brand_ids) > 0) {
            $b_products = Product::whereIn('brand_id', $brand_ids)->get();
            foreach ($b_products as $b_product) {
                $products[] = $b_product;
            }
        }
        foreach ($products as $product) {
            $product->update([
                'discount' => 0,
                'discount_type' => null,
                'discount_start_date' => null,
                'discount_end_date' => null
            ]);
        }
        $bulk_discount->delete();
        flash(translate('Bulk discount has been deleted successfully.'))->success();
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return redirect()->route('products.bulk-discount-form');
    }

    public function get_products_for_facebook_feed()
    {
        $products = Product::where('published', 1)->get();
        
        $headers = [
            'id', 'title', 'description', 'availability', 'condition', 'price', 
            'link', 'image_link', 'brand', 'google_product_category',
            'fb_product_category', 'quantity_to_sell_on_facebook', 'sale_price',
            'sale_price_effective_date', 'item_group_id', 'gender', 'color',
            'size', 'age_group', 'material', 'pattern', 'shipping',
            'shipping_weight', 'gtin', 'video_url', 'video_tag',
            'product_tags', 'style'
        ];

        $csvData = [];
        foreach ($products as $product) {
            $csvData[] = [
                $product->id,
                $product->name,
                $product->description,
                $product->current_stock > 0 ? 'in stock' : 'out of stock',
                'new',
                number_format($product->unit_price, 2) . ' ' . currency_symbol(),
                route('product', $product->slug),
                uploaded_asset($product->thumbnail_img),
                $product->brand ? $product->brand->name : '',
                '',
                '',
                // $product->category ? $product->category->name : '',
                // $product->category ? $product->category->name : '',
                $product->current_stock,
                $product->discount > 0 ? number_format($product->unit_price - $product->discount, 2) . ' ' .  currency_symbol() : '',
                '', // sale_price_effective_date
                $product->category_id, // item_group_id 
                '', // gender
                $product->colors, // color
                '', // size
                'adult',
                '', // material
                '', // pattern
                '', // shipping
                $product->weight . ' kg',
                $product->barcode,
                '', // video_url
                '', // video_tag
                implode(',', json_decode($product->tags) ?: []),
                '' // style
            ];
        }

        $filename = 'products_' . date('Y-m-d') . '.csv';
        $file = fopen($filename, 'w');
        
        fputcsv($file, $headers);
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return response()->download($filename)->deleteFileAfterSend();

    }

    public function get_products_feed() {
        $products = Product::where('published', 1)->get();
        $feed = LaravelGoogleShoppingFeed::init(
            env('APP_NAME'),
            'My Product Feed',
            route('google-merchant-feed')
        );
        
        foreach ($products as $product) {
            $photos = explode(',', $product->photos);
            
            $feed->addItem([
                'id' => $product->id,
                'title' => $product->name,
                'link' => route('product', $product->slug),
                'g:image_link' => count($photos) > 0 ? uploaded_asset($photos[0]): null,
                'g:price' => $product->unit_price,
            ]);
        }

        return $feed->generate();
    }
}
