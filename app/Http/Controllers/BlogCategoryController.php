<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use App\Models\BlogCategoryTranslation;

class BlogCategoryController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:view_blog_categories'])->only('index');
        $this->middleware(['permission:add_blog_category'])->only('create');
        $this->middleware(['permission:edit_blog_category'])->only('edit');
        $this->middleware(['permission:delete_blog_category'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $categories = BlogCategory::orderBy('category_name', 'asc');
        $lang = $request->lang;

        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('category_name', 'like', '%'.$sort_search.'%');
        }
        
        $categories = $categories->paginate(15);
        return view('backend.blog_system.category.index', compact('categories', 'sort_search', 'lang'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $all_categories = BlogCategory::all();
        return view('backend.blog_system.category.create', compact('all_categories'));
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
            'category_name' => 'required|max:255',
        ]);
        
        $category = new BlogCategory;
        
        $category->category_name = $request->category_name;
        $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->category_name));
        
        $category->save();

        foreach (\App\Models\Language::all() as $key => $language) {
            $category_translation = new \App\Models\BlogCategoryTranslation;
            $category_translation->category_name = $request->category_name;
            $category_translation->lang = $language->code;
            $category_translation->blog_category_id = $category->id;
            $category_translation->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->category_name));
            $category_translation->save();
        }
        
        
        flash(translate('Blog category has been created successfully'))->success();
        return redirect()->route('blog-category.index');
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
    public function edit(Request $request, $id)
    {
        $cateogry = BlogCategory::find($id);
        $all_categories = BlogCategory::all();
        $lang      = $request->lang;
        
        return view('backend.blog_system.category.edit',  compact('cateogry','all_categories', 'lang'));
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
            'category_name' => 'required|max:255',
        ]);

        $category = BlogCategory::find($id);

        if($request->lang == env("DEFAULT_LANGUAGE")){
            $category->category_name = $request->category_name;
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->category_name));
            $category->save();
        }

        $category_translation = BlogCategoryTranslation::where('blog_category_id', $category->id)->where('lang', $request->lang)->first();
        if($category_translation == null){
            $category_translation = new BlogCategoryTranslation;
            $category_translation->blog_category_id = $category->id;
            $category_translation->lang = $request->lang;
        }
        $category_translation->category_name = $request->category_name;
        $category_translation->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->category_name));
        $category_translation->save();
        
        flash(translate('Blog category has been updated successfully'))->success();
        return redirect()->route('blog-category.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        BlogCategory::find($id)->delete();
        
        return redirect('admin/blog-category');
    }
}
