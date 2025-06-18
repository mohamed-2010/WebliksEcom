<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\User;
use App\Models\ProductsImport;
use App\Models\ProductsExport;
use PDF;
use Illuminate\Support\Facades\Cache;
use Excel;
use Auth;

class ProductBulkUploadController extends Controller
{
    public function __construct() {

        $this->middleware(['permission:product_bulk_import'])->only('index');
        $this->middleware(['permission:product_bulk_export'])->only('export');
    }

    public function index()
    {
        if (Auth::user()->user_type == 'seller') {
            if(Auth::user()->shop->verification_status){
                return view('seller.product_bulk_upload.index');
            }
            else{
                flash('Your shop is not verified yet!')->warning();
                return back();
            }
        }
        elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('backend.product.bulk_upload.index');
        }
    }

    public function indexExport()
    {
        if (Auth::user()->user_type == 'seller') {
            if (Auth::user()->shop->verification_status) {
                return view('seller.product_bulk_upload.index');
            } else {
                flash('Your shop is not verified yet!')->warning();
                return back();
            }
        } elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            $categories = Category::where('parent_id', 0)->get();
            $brands = Brand::all();
            return view('backend.product.bulk_export.index', compact('categories', 'brands'));
        }
    }

    public function export(Request $request)
    {
        $exportType = $request->input('export_type', []);

        $categoryIds = (array) $request->input('category_id', []);
        $subcategoryIds = (array) $request->input('subcategory_id', []);
        $brandIds = (array) $request->input('brand_id', []);
        $formSwitcherValue = filter_var($request->input('exclude_outofstock', 'false'), FILTER_VALIDATE_BOOLEAN);
        $businessSetting = BusinessSetting::updateOrCreate(
            ['type' => 'exclude_outofstock'],
            ['value' => $formSwitcherValue ? 1 : 0]
        );
        $excludeOutOfStock = $businessSetting->value;

        return Excel::download(new ProductsExport($exportType, $categoryIds, $subcategoryIds, $brandIds, $excludeOutOfStock), 'products.xlsx');
    }

    public function getSubcategories(Request $request)
    {
        $categoryIds = $request->input('category_id', []);

        $subcategories = Category::whereIn('parent_id', $categoryIds)->get();
        return response()->json($subcategories);
    }


    public function pdf_download_category()
    {
        $categories = Category::all();

        return PDF::loadView('backend.downloads.category',[
            'categories' => $categories,
        ], [], [])->download('category.pdf');
    }

    public function pdf_download_brand()
    {
        $brands = Brand::all();

        return PDF::loadView('backend.downloads.brand',[
            'brands' => $brands,
        ], [], [])->download('brands.pdf');
    }

    public function pdf_download_seller()
    {
        $users = User::where('user_type','seller')->get();

        return PDF::loadView('backend.downloads.user',[
            'users' => $users,
        ], [], [])->download('user.pdf');

    }

    public function bulk_upload(Request $request)
    {
        if ($request->hasFile('bulk_file')) {
            $uploadType = $request->input('upload_type');

            if ($uploadType === 'edit') {
                $import = new ProductsImport(true);
            } else {
                $import = new ProductsImport(false);
            }

            Excel::import($import, request()->file('bulk_file'));

            $errors = $import->getErrors();
            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }
        }
        Cache::flush();

        flash(translate('Products imported successfully'))->success();
        return back();
    }

}
