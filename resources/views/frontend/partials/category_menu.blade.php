<!-- reomve class aiz-category-menu  bg-white rounded @if(Route::currentRouteName() == 'home') shadow-sm" @else shadow-lg @endif from div -->
<div class="{{$class ?? ''}}" id="category-sidebar" >
    {{--<div class="p-3 bg-soft-primary d-none d-lg-block rounded-top all-category position-relative text-left">
        <span class="fw-600 fs-16 mr-3">{{ translate('Categories') }}</span>
        <a href="{{ route('categories.all') }}" class="text-reset">
            <span class="d-none d-lg-inline-block">{{ translate('See All') }} ></span>
        </a>
    </div>--}}
    <ul class="list-unstyled categories no-scrollbar py-2 mb-0 text-left">
        @foreach (\App\Models\Category::where('level', 0)->orderBy('order_level', 'desc')->get() as $key => $category)
            <li class="category-nav-element" data-id="{{ $category->id }}">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('products.category', $category->slug) }}" class="text-truncate text-reset py-2 px-3 d-block">
                        <img
                            class="cat-image lazyload mr-2 opacity-60"
                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                            data-src="{{ uploaded_asset($category->icon) }}"
                            width="16"
                            alt="{{ $category->getTranslation('name') }}"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                        >
                        <span class="cat-name">{{ $category->getTranslation('name') }}</span>
                    </a>
                    @if($category->childrenCategories->isNotEmpty())
                        <a class="collapsed" data-toggle="collapse" href="#collapse-{{ $category->id }}" role="button" aria-expanded="false" aria-controls="collapse-{{ $category->id }}">
                        <span class="m-4" style="font-size: 20px;">+</span>
                        </a>
                    @endif
                </div>
                {{--@if(count(\App\Utility\CategoryUtility::get_immediate_children_ids($category->id))>0)--}}
                @if($category->childrenCategories->isNotEmpty())
                    <ul class="sub-categories collapse" id="collapse-{{ $category->id }}">
                        @foreach ($category->childrenCategories as $key => $childCategory)
                            <li>
                                <a href="{{ route('products.category', $childCategory->slug) }}" class="text-truncate text-reset py-2 px-3 d-block">
                                    <img
                                        class="cat-image lazyload mr-2 opacity-60"
                                        src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                        data-src="{{ uploaded_asset($childCategory->icon) }}"
                                        width="16"
                                        alt="{{ $childCategory->getTranslation('name') }}"
                                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"
                                    >
                                    <span class="cat-name">{{ $childCategory->getTranslation('name') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                <!-- removed class sub-cat-menu  shadow-lg -->
                    <!-- <div class="c-scrollbar-light rounded p-4">
                        <div class="c-preloader text-center absolute-center">
                            <i class="las la-spinner la-spin la-3x opacity-70"></i>
                        </div>
                    </div> -->
                @endif
            </li>
        @endforeach
    </ul>
</div>
