@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Blog Information')}}</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-fill border-light">
                    @foreach (\App\Models\Language::all() as $key => $language)
                        <li class="nav-item">
                            <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('blog.edit', ['blog'=>$blog->id, 'lang'=> $language->code] ) }}">
                                <img src="{{ asset('public/assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                                <span>{{ $language->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <form id="add_form" class="form-horizontal" action="{{ route('blog.update',$blog->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="title">{{ translate('Blog Title')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i></label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{ translate('Blog Title')}}" id="title" name="title" class="form-control" required value="{{ $blog->getTranslation('title', $lang) }}">
                        </div>
                    </div>
                    {{--<div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{translate('Blog Title')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Blog Title')}}" onkeyup="makeSlug(this.value)" id="title" name="title" value="{{ $blog->title }}" class="form-control" required>
                        </div>
                    </div>--}}
                    <div class="form-group row" id="category">
                        <label class="col-md-3 col-from-label">
                            {{translate('Category')}} 
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select
                                class="form-control aiz-selectpicker"
                                name="category_id"
                                id="category_id"
                                data-live-search="true"
                                required
                                @if($blog->category != null)
                                data-selected="{{ $blog->category->id }}"
                                @endif
                            >
                                <option>--</option>
                                @foreach ($blog_categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Slug')}}</label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Slug')}}" name="slug" id="slug" value="{{ $blog->getTranslation('slug', $lang) }}" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">
                            {{translate('Banner')}} 
                            <small>(1300x650)</small>
                        </label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ translate('Browse')}}
                                    </div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="banner" class="selected-files" value="{{ $blog->banner }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{translate('Short Description')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <textarea name="short_description" rows="5" class="form-control">{{ $blog->getTranslation('short_description', $lang) }}</textarea>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">
                            {{translate('Description')}}
                        </label>
                        <div class="col-md-9">
                            <textarea class="aiz-text-editor" name="description">{{ $blog->getTranslation('description', $lang) }}</textarea>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Meta Title')}}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="meta_title" value="{{ $blog->getTranslation('meta_title', $lang) }}" placeholder="{{translate('Meta Title')}}">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">
                            {{translate('Meta Image')}} 
                            <small>(200x200)+</small>
                        </label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ translate('Browse')}}
                                    </div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="meta_img" class="selected-files" value="{{ $blog->meta_img }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Meta Description')}}</label>
                        <div class="col-md-9">
                            <textarea name="meta_description" rows="5" class="form-control">{{ $blog->getTranslation('meta_description', $lang) }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{translate('Meta Keywords')}}
                        </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ $blog->getTranslation('meta_keywords', $lang) }}" placeholder="{{translate('Meta Keywords')}}">
                        </div>
                    </div>
                    
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">
                            {{translate('Save')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function makeSlug(val) {
        let str = val;
        let output = str.replace(/\s+/g, '-').toLowerCase();
        $('#slug').val(output);
    }
</script>
@endsection
