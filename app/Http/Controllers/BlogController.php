<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Models\BlogTranslation;

class BlogController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:view_blogs'])->only('index');
        $this->middleware(['permission:add_blog'])->only('create');
        $this->middleware(['permission:edit_blog'])->only('edit');
        $this->middleware(['permission:delete_blog'])->only('destroy');
        $this->middleware(['permission:publish_blog'])->only('change_status');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $blogs = Blog::orderBy('created_at', 'desc');
        
        if ($request->search != null){
            $blogs = $blogs->where('title', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }

        $blogs = $blogs->paginate(15);

        return view('backend.blog_system.blog.index', compact('blogs','sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $blog_categories = BlogCategory::all();
        return view('backend.blog_system.blog.create', compact('blog_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'category_id' => 'required',
            'title' => 'required|max:255',
        ]);

        $blog = new Blog;
        
        $blog->category_id = $request->category_id;
        $blog->title = $request->title;
        $blog->banner = $request->banner;
        $blog->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $blog->short_description = $request->short_description;
        $blog->description = $request->description;
        
        $blog->meta_title = $request->meta_title ?? "";
        $blog->meta_img = $request->meta_img ?? "";
        $blog->meta_description = $request->meta_description ?? "";
        $blog->meta_keywords = $request->meta_keywords ?? "";
        
        $blog->save();

        foreach (\App\Models\Language::all() as $key => $language) {
            $blog_translation = BlogTranslation::firstOrNew(['lang' => $language->code, 'blog_id' => $blog->id]);
            //name is array for every language
            // $blog_translation->title = $request->title[$key];
            // $blog_translation->slug = $request->slug[$key];
            // $blog_translation->short_description = $request->short_description[$key] ?? "";
            // $blog_translation->description = $request->description[$key] ?? "";
            // $blog_translation->meta_title = $request->meta_title[$key] ?? "";
            // $blog_translation->meta_description = $request->meta_description[$key] ?? "";
            // $blog_translation->meta_keywords = $request->meta_keywords[$key] ?? "";
            // $blog_translation->save();
            $blog_translation->title = $request->title;
            $blog_translation->slug = $request->slug;
            $blog_translation->short_description = $request->short_description ?? "";
            $blog_translation->description = $request->description ?? "";
            $blog_translation->meta_title = $request->meta_title ?? "";
            $blog_translation->meta_description = $request->meta_description ?? "";
            $blog_translation->meta_keywords = $request->meta_keywords ?? "";
            $blog_translation->save();
        }

        flash(translate('Blog post has been created successfully'))->success();
        return redirect()->route('blog.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang      = $request->lang;
        $blog = Blog::find($id);
        $blog_categories = BlogCategory::all();
        
        return view('backend.blog_system.blog.edit', compact('blog','blog_categories', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {        
        $request->validate([
            'category_id' => 'required',
            'title' => 'required|max:255',
        ]);

        $blog = Blog::find($id);

        if($request->lang == env("DEFAULT_LANGUAGE")){
            $blog->category_id = $request->category_id;
            $blog->title = $request->title;
            $blog->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
            $blog->short_description = $request->short_description;
            $blog->description = $request->description;
            
            $blog->meta_title = $request->meta_title;
            $blog->meta_description = $request->meta_description;
            $blog->meta_keywords = $request->meta_keywords;
            
            $blog->save();
        }

        $blog->meta_img = $request->meta_img;
        $blog->banner = $request->banner;

        $blog_translation = BlogTranslation::firstOrNew(['lang' => $request->lang, 'blog_id' => $blog->id]);
        $blog_translation->title = $request->title;
        $blog_translation->slug = $request->slug;
        $blog_translation->short_description = $request->short_description ?? "";
        $blog_translation->description = $request->description ?? "";
        $blog_translation->meta_title = $request->meta_title ?? "";
        $blog_translation->meta_description = $request->meta_description ?? "";
        $blog_translation->meta_keywords = $request->meta_keywords ?? "";
        $blog_translation->save();

        flash(translate('Blog post has been updated successfully'))->success();
        return redirect()->route('blog.index');
    }
    
    public function change_status(Request $request) {
        $blog = Blog::find($request->id);
        $blog->status = $request->status;
        
        $blog->save();
        return 1;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Blog::find($id)->delete();
        
        return redirect()->back();
    }


    public function all_blog(Request $request) {
        $lang = $request->lang;
        $blogs = Blog::where('status', 1)->orderBy('created_at', 'desc')->paginate(12);
        return view("frontend.blog.listing", compact('blogs', 'lang'));
    }
    
    public function blog_details(Request $request, $slug) {
        $lang = $request->lang;
        if($lang == env("DEFAULT_LANGUAGE")){
            $blog = Blog::where('slug', $slug)->first();
        } else {
            $blog = BlogTranslation::where('slug', $slug)->first()->blog;
        }
        return view("frontend.blog.details", compact('blog', 'lang'));
    }
}
