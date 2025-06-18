<?php

namespace App\Http\Controllers;

use App\Models\HomeNavbarTrans;
use App\Models\HomeNavbarTranslation;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

use App\Models\Language;
use App\Models\Navbar;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
	public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:header_setup'])->only('header');
        $this->middleware(['permission:footer_setup'])->only('footer');
        $this->middleware(['permission:view_all_website_pages'])->only('pages');
        $this->middleware(['permission:website_appearance'])->only('appearance');
    }

	public function header(Request $request)
	{
		$lang = $request->lang ?? app()->getLocale();
		// Get translations for the current language
		$navbars = HomeNavbarTrans::where('lang', $lang)->get();
		// Fallback to default language if no translations exist
		if ($lang === 'sa' && $navbars->isEmpty()) {
			$defaultNavbars = HomeNavbarTrans::where('lang', 'en')->get();
			$navbars = $defaultNavbars->map(function ($navbar) {
				HomeNavbarTrans::create([
					'label' => '', // Placeholder for user input
					'link' => $navbar->link,
					'lang' => 'sa', // No ID for new translations
				]);
			});
		}
		return view('backend.website_settings.header', compact('lang', 'navbars'));
	}
	// {

    //     $language = Language::where('code', $request->lang)->first();
    //     $lang =$request->lang;
    //     $navbars=HomeNavbarTrans::where('lang', $lang ?? app()->getLocale())->get();
	// 	return view('backend.website_settings.header',compact('lang','language','navbars'));
	// }
	public function footer(Request $request)
	{
		$lang = $request->lang;
		return view('backend.website_settings.footer', compact('lang'));
	}
	public function pages(Request $request)
	{
		return view('backend.website_settings.pages.index');
	}
	public function appearance(Request $request)
	{
        $lang = $request->lang;
		return view('backend.website_settings.appearance', compact('lang'));
	}
}