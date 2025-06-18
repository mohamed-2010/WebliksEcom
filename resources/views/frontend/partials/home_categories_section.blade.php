@if(get_setting('home_categories') != null) 
    @php $home_categories = json_decode(get_setting('home_categories')); @endphp
    @foreach ($home_categories as $key => $value)
     @php $category = \App\Models\Category::find($value); @endphp
            @if($key == 3)
            {{-- Banner Section 2 --}}
            @if (get_setting('home_banner3_images') != null)
                <div class="mb-4">
                    <div class="container">
                        <div class="row gutters-10">
                            @php $banner_3_imags = json_decode(get_setting('home_banner3_images')); @endphp
                            @foreach ($banner_3_imags as $key => $value)
                                <div class="col-xl col-md-6">
                                    <div class="mb-3 mb-lg-0">
                                        <a href="{{ json_decode(get_setting('home_banner3_links'), true)[$key] }}" class="d-block text-reset">
                                            <img src="{{ static_asset('assets/img/placeholder-rect.jpg') }}" data-src="{{ uploaded_asset($banner_3_imags[$key]) }}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload w-100">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        @endif 
       
        <section class="mb-4">
            <div class="container">
                <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                    <div class="d-flex mb-3 align-items-baseline border-bottom">
                        <h3 class="h5 fw-700 mb-0">
                            <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ $category->getTranslation('name') }}</span>
                        </h3>
                        <a href="{{ route('products.category', $category->slug) }}" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('View More') }}</a>
                    </div>
                    <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="6" data-xl-items="5" data-lg-items="4"  data-md-items="3" data-sm-items="2" data-xs-items="2" data-arrows='true'>
                        @foreach (get_cached_products($category->id) as $key => $product)
                            <div class="carousel-box">
                                @include('frontend.partials.product_box_1',['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endforeach
@endif

