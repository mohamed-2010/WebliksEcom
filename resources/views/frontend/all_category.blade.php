@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('All Categories') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('categories.all') }}">"{{ translate('All Categories') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
    <div class="container ">
        <div class=" justify-content-around"  style="display: flex;
    align-content: center;
    justify-content: flex-start;
    flex-wrap: wrap;">
        @foreach ($categories as $key => $category)
            <div class="mb-3  @if(!\App\Utility\CategoryUtility::get_immediate_children_ids($category->id)) col-lg-5 @else col-lg-11 @endif bg-white shadow-sm rounded"
            >
                <div class="p-3 border-bottom fs-16 fw-600 text-dark p-4 d-flex align-items-center">
                        <div class="size-100px overflow-hidden p-1 border mr-3">
                             <img src="{{ uploaded_asset($category->banner) }}" class="img-fit h-100"
                                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder-rect.jpg') }}';"
                                        >
                   
                        </div>
                         <a href="{{ route('products.category', $category->slug) }}" class="text-reset h4">{{  $category->getTranslation('name') }}</a>
                </div>
               @if(\App\Utility\CategoryUtility::get_immediate_children_ids($category->id))
                <div class="p-3 p-lg-4"
                >
                    <div class="row">
                        @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($category->id) as $key => $first_level_id)
                        <div class="col-lg-4 col-6 text-left">
                            <h6 class="mb-3"><a class="text-reset fw-600 fs-14" href="{{ route('products.category', \App\Models\Category::find($first_level_id)->slug) }}">{{ \App\Models\Category::find($first_level_id)->getTranslation('name') }}</a></h6>
                            <ul class="mb-3 list-unstyled pl-2">
                                @foreach (\App\Utility\CategoryUtility::get_immediate_children_ids($first_level_id) as $key => $second_level_id)
                                <li class="mb-2">
                                    <a class="text-reset" href="{{ route('products.category', \App\Models\Category::find($second_level_id)->slug) }}" >{{ \App\Models\Category::find($second_level_id)->getTranslation('name') }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        @endforeach
        </div>
    </div>
</section>

@endsection
