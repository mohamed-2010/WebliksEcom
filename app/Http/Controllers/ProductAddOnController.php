<?php

namespace App\Http\Controllers;

use App\Models\ProductAddOn;
use App\Models\ProductAddOnTranslation;
use Illuminate\Http\Request;

class ProductAddOnController extends Controller
{
    public function index($category_id)
    {
        //paginate(15)
        $productAddOns = ProductAddOn::where('category_add_on_id', $category_id)->paginate(15);
        return view('backend.product_add_on.index', compact('productAddOns', 'category_id'));
    }

    public function create($category_id)
    {
        return view('backend.product_add_on.create', compact('category_id'));
    }

    public function store(Request $request, $category_id)
    {
        $request->validate([
            'name' => 'required|unique:product_add_ons,name',
            'price' => 'required|numeric',
        ]);

        ProductAddOn::create([
            'name' => $request->name,
            'price' => $request->price,
            'category_add_on_id' => $category_id,
        ]);

        return redirect()->route('product_addon.index', $category_id)->with('success', 'Product Add On created successfully.');
    }

    public function edit($category_id, $productAddOn, Request $request)
    {
        $lang = $request->lang;
        $productAddOn = ProductAddOn::find($productAddOn);
        return view('backend.product_add_on.edit', compact('productAddOn', 'category_id', 'lang'));
    }

    public function update(Request $request, $category_id, $productAddOn)
    {
        $request->validate([
            'name' => 'required|unique:product_add_ons,name,' . $productAddOn,
            'price' => 'required|numeric',
        ]);
        $productAddOn = ProductAddOn::find($productAddOn);

        if($request->lang == env("DEFAULT_LANGUAGE")){
            $productAddOn->name = $request->name;
        }

        $productAddOn->update([
            'price' => $request->price,
        ]);

        $product_addon_translation = ProductAddOnTranslation::firstOrNew(['lang' => $request->lang, 'product_add_on_id' => $productAddOn->id]);
        $product_addon_translation->name = $request->name;
        $product_addon_translation->save();

        flash(translate('PeoductAddOn has been updated successfully'))->success();

        return redirect()->route('product_addon.index', $category_id)->with('success', 'Product Add On updated successfully.');
    }

    public function destroy($category_id, $productAddOn)
    {
        $productAddOn = ProductAddOn::find($productAddOn);

        // Product Addon Translations Delete
        foreach ($productAddOn->product_addon_translations as $key => $product_addon_translation) {
            $product_addon_translation->delete();
        }

        $productAddOn->delete();
        return redirect()->route('product_addon.index', $category_id)->with('success', 'Product Add On deleted successfully.');
    }
}
