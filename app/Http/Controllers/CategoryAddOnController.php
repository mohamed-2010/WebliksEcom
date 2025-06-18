<?php

namespace App\Http\Controllers;

use App\Models\CategoryAddon;
use App\Models\CategoryAddonTranslation;
use Illuminate\Http\Request;

class CategoryAddOnController extends Controller
{
    public function index()
    {
        $categoryAddOns = CategoryAddon::paginate(15);
        return view('backend.category_add_on.index', compact('categoryAddOns'));
    }

    public function create()
    {
        return view('backend.category_add_on.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:category_add_on,name',
            'price' => 'required'
        ]);

        $category = CategoryAddon::create([
            'name' => $request->name,
            'price' => $request->price
        ]);

        $category_translation = CategoryAddonTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'category_addon_id' => $category->id]);
        $category_translation->name = $request->name;
        $category_translation->save();

        flash(translate('CategoryAddOn has been inserted successfully'))->success();

        return redirect()->route('category_addons.index')->with('success', 'Category Add On created successfully.');
    }

    public function edit($categoryAddOn, Request $request)
    {
        $lang = $request->lang;
        $categoryAddOn = CategoryAddon::find($categoryAddOn);
        return view('backend.category_add_on.edit', compact('categoryAddOn', 'lang'));
    }

    public function update(Request $request, $categoryAddOn)
    {

        $categoryAddOn = CategoryAddon::find($categoryAddOn);
        $categoryAddOn->price = $request->price;
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $categoryAddOn->name = $request->name;
        }
        $categoryAddOn->save();

        $category_translation = CategoryAddonTranslation::firstOrNew(['lang' => $request->lang, 'category_addon_id' => $categoryAddOn->id]);
        $category_translation->name = $request->name;
        $category_translation->save();

        flash(translate('CategoryAddOn has been updated successfully'))->success();

        return redirect()->route('category_addons.index')->with('success', 'Category Add On updated successfully.');
    }

    public function destroy($categoryAddOn)
    {
        $categoryAddOn = CategoryAddon::find($categoryAddOn);

        // Category Translations Delete
        foreach ($categoryAddOn->category_addon_translations as $key => $category_translation) {
            $category_translation->delete();
        }

        $categoryAddOn->delete();
        return redirect()->route('category_addons.index')->with('success', 'Category Add On deleted successfully.');
    }
}
