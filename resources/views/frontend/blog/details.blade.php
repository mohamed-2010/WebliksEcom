@extends('frontend.layouts.app')

@section('meta_title'){{ $blog->getTranslation('meta_title', $lang) }}@stop

@section('meta_description'){{ $blog->getTranslation('meta_description', $lang) }}@stop

@section('meta_keywords'){{ $blog->getTranslation('meta_keywords', $lang) }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $blog->getTranslation('meta_title', $lang) }}">
    <meta itemprop="description" content="{{ $blog->getTranslation('meta_description', $lang) }}">
    <meta itemprop="image" content="{{ uploaded_asset($blog->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $blog->getTranslation('meta_title', $lang) }}">
    <meta name="twitter:description" content="{{ $blog->getTranslation('meta_description', $lang) }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ uploaded_asset($blog->meta_img) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $blog->getTranslation('meta_title', $lang) }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ route('blog.details', $blog->getTranslation('slug', $lang)) }}" />
    <meta property="og:image" content="{{ uploaded_asset($blog->meta_img) }}" />
    <meta property="og:description" content="{{ $blog->getTranslation('meta_description', $lang) }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
@endsection

@section('content')

<section class="py-4">
    <div class="container">
        <div class="mb-4">
            <img
                src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                data-src="{{ uploaded_asset($blog->banner) }}"
                alt="{{ $blog->getTranslation('title', $lang) }}"
                class="img-fluid lazyload w-100"
            >
        </div>
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="bg-white rounded shadow-sm p-4"> 
                    <div class="border-bottom">
                        <h1 class="h4">
                            {{ $blog->getTranslation('title', $lang) }}
                        </h1>

                        @if($blog->category != null)
                        <div class="mb-2 opacity-50">
                            @php
                                $category = \App\Models\BlogCategory::find($blog->category_id);
                            @endphp
                            <i>{{ $category->getTranslation('category_name', $lang) }}</i>
                        </div>
                        @endif
                    </div>
                    <div class="mb-4 overflow-hidden">
                        {!! $blog->getTranslation('description', $lang) !!}
                    </div>
                    
                    @if (get_setting('facebook_comment') == 1)
                    <div>
                        <div class="fb-comments" data-href="{{ route("blog",$blog->getTranslation('slug', $lang)) }}" data-width="" data-numposts="5"></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@endsection


@section('script')
    @if (get_setting('facebook_comment') == 1)
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0&appId={{ env('FACEBOOK_APP_ID') }}&autoLogAppEvents=1" nonce="ji6tXwgZ"></script>
    @endif
@endsection