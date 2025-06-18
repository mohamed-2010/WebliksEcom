<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\HomeNavbarTrans;
use App\Models\HomeNavbarTranslation;
use App\Models\Language;
use App\Models\Navbar;
use Artisan;
use Illuminate\Http\Request;

class HomeNavbarTranslationController extends Controller
{
    public function store(Request $request)
    {
        // return dd($request->all());
        // Custom validation rules
        $request->validate([
            'header_menu_labels' => 'required|array',
            'header_menu_labels.*' => 'required|string|max:255',
            'header_menu_links' => 'required|array',
            'header_menu_links.*' => 'required',
        ]);
    
        // Retrieve existing record
        // $businessSetting = BusinessSetting::where('type', 'header_menu_labels')->first();
    
        // Initialize data structure
        $headerMenu = [];
        // if ($businessSetting) {
        //     $headerMenu = json_decode($businessSetting->value, true);
        // }
    
        // Update for all active languages
        $activeLangs = Language::where('status', 1)->pluck('code')->toArray();
    
        foreach ($activeLangs as $lang) {
            // if($lang == $request->lang) {
                HomeNavbarTrans::create([
                    'labels' => $request->header_menu_labels,
                    'links' => $request->header_menu_links,
                    'lang' => $lang,
                ]);
            // }
        }

        // clear cache
        Artisan::call('cache:clear');
    
        // Flash success message
        return redirect()->back()->with('success', 'Header navigation menu updated successfully.');
    }    
    
    public function destroy($id)
    {

        $navbar = HomeNavbarTrans::find($id);
        if ($navbar) {
            // Delete the navbar item
            $navbar->delete();
            // Return a success response
            return response()->json(['success' => true]);
        }
        // If item not found, return an error response
        return response()->json(['success' => false], 404);
    }




}

