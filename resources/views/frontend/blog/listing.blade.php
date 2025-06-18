@extends('frontend.layouts.app')

@section('content')

<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('Blog')}}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">
                            {{ translate('Home')}}
                        </a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('blog') }}">
                            "{{ translate('Blog') }}"
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="pb-4">
    <div class="container">
        <div class="blog card-columns">
            @foreach($blogs as $blog)
                <div class="card mb-4 overflow-hidden shadow-none border rounded-0 hov-scale-img p-3">
                    <a href="{{ route('blog.details', ['slug' => $blog->getTranslation('slug', $lang)]) }}" class="text-reset d-block overflow-hidden h-180px">
                        <img
                            src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                            data-src="{{ uploaded_asset($blog->banner) }}"
                            alt="{{ $blog->getTranslation('title', $lang) }}"
                            class="img-fluid lazyload "
                        >
                    </a>
                    <div class="py-3">
                        <h2 class="fs-16 fw-700 mb-3 h-35px text-truncate-2">
                            <a href="{{ route('blog.details', ['slug' => $blog->getTranslation('slug', $lang)]) }}" class="text-reset">
                                {{ $blog->getTranslation('title', $lang) }}
                            </a>
                        </h2>

                        <p class="opacity-70 mb-3 h-60px text-truncate-3">
                            {{ $blog->getTranslation('short_description', $lang) }}
                        </p>
                        <div>
                            <small class="fs-12 fw-400 opacity-60">{{ $blog->created_at->format('M d, Y') }}</small>
                        </div>
                        @if($blog->category != null)
                        <div class="mb-2 opacity-50">
                            @php
                                $category = \App\Models\BlogCategory::find($blog->category_id);
                            @endphp
                            <small class="fs-12 fw-400 text-blue">{{ $category->getTranslation('category_name', $lang) }}</small>
                        </div>
                        @endif
                        <div class="mt-3 text-primary d-flex">
                            <a href="{{ route('blog.details', ['slug' => $blog->getTranslation('slug', $lang)]) }}" class="fs-14 fw-700 text-primary has-transition d-flex align-items-center hov-column-gap-1">
                                {{ translate('View More') }}
                            </a>
                            <i class="las las-2x la-arrow-right fs-24 ml-1"></i>
                        </div>

                    </div>
                </div>
            @endforeach
            
        </div>
        <div class="aiz-pagination aiz-pagination-center mt-4">
            {{ $blogs->links() }}
        </div>
    </div>
</section>
@endsection
