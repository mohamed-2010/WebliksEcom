<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeaderLink;
use App\Models\HeaderLinkTranslation;
use App\Models\Language;

class HeaderLinkAjaxController extends Controller
{
    /**
     * Create or Update a single header link with all languages in one request.
     * 
     * Expecting form data:
     *  - id (optional): if present, we update that link
     *  - title[lang_code]: Title for each language
     *  - link[lang_code]: Link for each language
     */
    public function saveLink(Request $request)
    {
        // If 'id' is present, we update; otherwise we create a new record
        $headerLink = $request->id 
            ? HeaderLink::findOrFail($request->id)
            : new HeaderLink;

        // Save main table fields if any (e.g., sort_order). None here.
        $headerLink->save();

        // For each language, upsert the translation
        $langs = Language::all();
        foreach ($langs as $lang) {
            $langCode = $lang->code;

            // We expect $request->title[$langCode] and $request->link[$langCode]
            $titleVal = $request->input("title.{$langCode}", null);
            $linkVal  = $request->input("link.{$langCode}", null);

            // Either create or update the existing translation row
            $translation = HeaderLinkTranslation::firstOrNew([
                'header_link_id' => $headerLink->id,
                'lang' => $langCode,
            ]);

            $translation->slug = $titleVal;
            $translation->url  = $linkVal;
            $translation->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Header link saved successfully!',
            'data'    => $headerLink->load('translations')
        ]);
    }

    /**
     * Delete (soft-delete) a header link.
     */
    public function deleteLink($id)
    {
        $headerLink = HeaderLink::findOrFail($id);
        $headerLink->delete();

        return response()->json([
            'success' => true,
            'message' => 'Header link deleted.'
        ]);
    }

    /**
     * Show a single link (with translations) for editing.
     */
    public function show($id)
    {
        $link = HeaderLink::with('translations')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $link
        ]);
    }
}
