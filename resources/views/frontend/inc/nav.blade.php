<style>
    .aiz-main-wrapper {
        min-height: auto;
    }
</style>
@if (get_setting('topbar_banner') != null)
    <div class="position-relative top-banner z-3" data-key="top-banner" data-value="removed">
        <a href="{{ get_setting('topbar_banner_link') }}" class="d-block text-reset">
            <img src="{{ uploaded_asset(get_setting('topbar_banner')) }}" class="w-100 mw-100 h-50px h-lg-auto img-fit">
        </a>
        <button class="btn text-white absolute-top-right set-session" data-key="top-banner" data-value="removed"
            data-toggle="remove-parent" data-parent=".top-banner">
            <i class="la la-close la-2x"></i>
        </button>
    </div>
@endif
<!-- Top Bar -->
<div class="top-navbar bg-white border-bottom border-soft-secondary z-1030 h-35px h-sm-auto">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col">
                <ul class="list-inline d-flex justify-content-between justify-content-lg-start mb-0">
                    @if (get_setting('show_language_switcher') == 'on')
                        {{--                    <li class="list-inline-item dropdown mr-3" id="lang-change">
                        @php
                            if(Session::has('locale')){
                                $locale = LaravelLocalization::getCurrentLocale();
                            }
                            else{
                                $locale = 'en';
                            }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2" data-toggle="dropdown" data-display="static">
                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" class="mr-2 lazyload" alt="{{ \App\Models\Language::where('code', $locale)->first()->name }}" height="11">
                            <span class="opacity-60">{{ \App\Models\Language::where('code', $locale)->first()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-left" style="z-index: 10000;">
                            @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                                <li>
                                    <a href="javascript:void(0)" data-flag="{{ $language->code }}" class="dropdown-item @if ($locale == $language) active @endif">
                                        <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-1 lazyload" alt="{{ $language->name }}" height="11">
                                        <span class="language">{{ $language->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li> --}}
                        <li class="list-inline-item dropdown mr-3" id="lang-chan">
                            <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2" data-toggle="dropdown"
                                data-display="static">
                                <img src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                    data-src="{{ static_asset('assets/img/flags/' . LaravelLocalization::getCurrentLocale() . '.png') }}"
                                    class="mr-2 lazyload" alt="{{ LaravelLocalization::getCurrentLocaleNative() }}"
                                    height="11">
                                <span class="opacity-60">{{ LaravelLocalization::getCurrentLocaleNative() }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-left" style="z-index: 10000;">
                                @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <li id="lang-chang"
                                        data-flag="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                        <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                            class="dropdown-item @if (LaravelLocalization::getCurrentLocale() == $localeCode) active @endif"
                                            data-flag="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ static_asset('assets/img/flags/' . $localeCode . '.png') }}"
                                                class="mr-1 lazyload" alt="{{ $properties['native'] }}" height="11">
                                            <span class="language">{{ $properties['native'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif

                    @if (get_setting('show_currency_switcher') == 'on')
                        <li class="list-inline-item dropdown ml-auto ml-lg-0 mr-0" id="currency-change">
                            @php
                                if (Session::has('currency_code')) {
                                    $currency_code = Session::get('currency_code');
                                } else {
                                    $currency_code = \App\Models\Currency::findOrFail(
                                        get_setting('system_default_currency'),
                                    )->code;
                                }
                            @endphp
                            <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2 opacity-60"
                                data-toggle="dropdown" data-display="static">
                                {{ \App\Models\Currency::where('code', $currency_code)->first()->name }}
                                {{ \App\Models\Currency::where('code', $currency_code)->first()->symbol }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left">
                                @foreach (\App\Models\Currency::where('status', 1)->get() as $key => $currency)
                                    <li>
                                        <a class="dropdown-item @if ($currency_code == $currency->code) active @endif"
                                            href="javascript:void(0)"
                                            data-currency="{{ $currency->code }}">{{ $currency->name }}
                                            ({{ $currency->symbol }})</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="col-5 text-right d-none d-lg-block">
                <ul class="list-inline mb-0 h-100 d-flex justify-content-end align-items-center">
                    @if (get_setting('helpline_number'))
                        <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="tel:{{ get_setting('helpline_number') }}"
                                class="text-reset d-inline-block opacity-60 py-2">
                                <i class="la la-phone"></i>
                                <span>{{ translate('Help line') }}</span>
                                <span>{{ get_setting('helpline_number') }}</span>
                            </a>
                        </li>
                    @endif
                    @auth
                        @if (isAdmin())
                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                <a href="{{ route('admin.dashboard') }}"
                                    class="text-reset d-inline-block opacity-60 py-2">{{ translate('My Panel') }}</a>
                            </li>
                        @else
                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0 dropdown">
                                <a class="dropdown-toggle no-arrow text-reset" data-toggle="dropdown"
                                    href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                    <span class="">
                                        <span class="position-relative d-inline-block">
                                            <i class="las la-bell fs-18"></i>
                                            @if (count(Auth::user()->unreadNotifications) > 0)
                                                <span
                                                    class="badge badge-sm badge-dot badge-circle badge-primary position-absolute absolute-top-right"></span>
                                            @endif
                                        </span>
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg py-0"
                                    style="z-index: 10000;">
                                    <div class="p-3 bg-light border-bottom">
                                        <h6 class="mb-0">{{ translate('Notifications') }}</h6>
                                    </div>
                                    <div class="px-3 c-scrollbar-light overflow-auto " style="max-height:300px;">
                                        <ul class="list-group list-group-flush">
                                            @forelse(Auth::user()->unreadNotifications as $notification)
                                                <li class="list-group-item">
                                                    @if ($notification->type == 'App\Notifications\OrderNotification')
                                                        @if (Auth::user()->user_type == 'customer')
                                                            <a href="{{ route('purchase_history.details', encrypt($notification->data['order_id'])) }}"
                                                                class="text-reset">
                                                                <span class="ml-2">
                                                                    {{ translate('Order code: ') }}
                                                                    {{ $notification->data['order_code'] }}
                                                                    {{ translate('has been ' . ucfirst(str_replace('_', ' ', $notification->data['status']))) }}
                                                                </span>
                                                            </a>
                                                        @elseif (Auth::user()->user_type == 'seller')
                                                            <a href="{{ route('seller.orders.show', encrypt($notification->data['order_id'])) }}"
                                                                class="text-reset">
                                                                <span class="ml-2">
                                                                    {{ translate('Order code: ') }}
                                                                    {{ $notification->data['order_code'] }}
                                                                    {{ translate('has been ' . ucfirst(str_replace('_', ' ', $notification->data['status']))) }}
                                                                </span>
                                                            </a>
                                                        @endif
                                                    @endif
                                                </li>
                                            @empty
                                                <li class="list-group-item">
                                                    <div class="py-4 text-center fs-16">
                                                        {{ translate('No notification found') }}
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div class="text-center border-top">
                                        <a href="{{ route('all-notifications') }}" class="text-reset d-block py-2">
                                            {{ translate('View All Notifications') }}
                                        </a>
                                    </div>
                                </div>
                            </li>

                            <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                                @if (Auth::user()->user_type == 'seller')
                                    <a href="{{ route('seller.dashboard') }}"
                                        class="text-reset d-inline-block opacity-60 py-2">{{ translate('My Panel') }}</a>
                                @else
                                    <a href="{{ route('dashboard') }}"
                                        class="text-reset d-inline-block opacity-60 py-2">{{ translate('My Panel') }}</a>
                                @endif
                            </li>
                        @endif
                        <li class="list-inline-item">
                            <a href="{{ route('logout') }}"
                                class="text-reset d-inline-block opacity-60 py-2">{{ translate('Logout') }}</a>
                        </li>
                    @else
                        <li class="list-inline-item mr-3 border-right border-left-0 pr-3 pl-0">
                            <a href="{{ route('user.login') }}"
                                class="text-reset d-inline-block opacity-60 py-2">{{ translate('Login') }}</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ route('user.registration') }}"
                                class="text-reset d-inline-block opacity-60 py-2">{{ translate('Registration') }}</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- END Top Bar -->
<header class="@if (get_setting('header_stikcy') == 'on') sticky-top @endif z-1020 bg-white border-bottom shadow-sm">
    <div class="position-relative logo-bar-area z-1">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">

                <div class="d-lg-none ml-0 mr-0">
                    <a class="p-2 d-block text-reset" href="javascript:void(0);" data-toggle="class-toggle"
                        data-target=".aiz-categories-sidebar" data-same=".filter-sidebar-thumb">
                        <i class="las la-align-justify la-flip-horizontal la-2x text-white"></i>
                    </a>
                </div>

                <div class="col-auto col-xl-3 pl-0 d-flex align-items-center">
                    <a class="d-block py-20px ml-0" href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if ($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}"
                                class="mw-100 h-30px h-md-40px" height="40">
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}"
                                class="mw-100 h-30px h-md-40px" height="40">
                        @endif
                    </a>

                    {{-- <!--@if (Route::currentRouteName() != 'home')-->
                        <div class="d-none d-xl-block align-self-stretch category-menu-icon-box ml-auto mr-0">
                            <div class="h-100 d-flex align-items-center" id="category-menu-icon">
                                <div class="dropdown-toggle navbar-light bg-light h-40px w-50px pl-2 rounded border c-pointer">
                                    <span class="navbar-toggler-icon"></span>
                                </div>
                            </div>
                        </div>
                    <!--@endif--> --}}
                </div>
                {{-- <div class="d-lg-none ml-auto mr-0">
                    <a class="p-2 d-block text-reset" href="javascript:void(0);" data-toggle="class-toggle" data-target=".front-header-search">
                        <i class="las la-search la-flip-horizontal la-2x"></i>
                    </a>
                </div> --}}

                <div class="flex-grow-1 front-header-search d-none d-lg-flex align-items-center bg-white">
                    <div class="position-relative flex-grow-1">
                        <form action="{{ route('search') }}" method="GET" class="stop-propagation">
                            <div class="d-flex position-relative align-items-center">
                                <div class="d-lg-none" data-toggle="class-toggle" data-target=".front-header-search">
                                    <button class="btn px-2" type="button"><i
                                            class="la la-2x la-long-arrow-left"></i></button>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="border-0 border-lg form-control" id="search"
                                        name="keyword"
                                        @isset($query)
                                        value="{{ $query }}"
                                    @endisset
                                        placeholder="{{ translate('I am shopping for...') }}" autocomplete="off">
                                    <div class="input-group-append d-none d-lg-block">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="la la-search la-flip-horizontal fs-18"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="typed-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100"
                            style="min-height: 200px">
                            <div class="search-preloader absolute-top-center">
                                <div class="dot-loader">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                            <div class="search-nothing d-none p-3 text-center fs-16">

                            </div>
                            <div id="search-content" class="text-left">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-none d-lg-none ml-2 mr-0">
                    <div class="nav-search-box">
                        <a href="#" class="nav-box-link">
                            <i class="la la-search la-flip-horizontal d-inline-block nav-box-icon"></i>
                        </a>
                    </div>
                </div>

                <div class=" d-lg-block ml-2 mr-0">
                    <div class="" id="compare">
                        @include('frontend.partials.compare')
                    </div>
                </div>

                <div class=" d-lg-block ml-2 mr-0">
                    <div class="" id="wishlist">
                        @include('frontend.partials.wishlist')
                    </div>
                </div>

                <div class="d-none d-lg-block  align-self-stretch ml-3 mr-0" data-hover="dropdown">
                    <div class="nav-cart-box dropdown h-100" id="cart_items">
                        @include('frontend.partials.cart')
                    </div>
                </div>

            </div>
        </div>
        <div class="d-flex align-items-center bg-white d-md-none">
            <div class="flex-grow-1">
                <form action="{{ route('search') }}" method="GET" class="stop-propagation">
                    <div class="d-flex align-items-center">
                        <div class="input-group">
                            <input type="text" class="border-0 border-lg form-control" id="moblieSearch"
                                name="keyword"
                                @isset($query)
                                        value="{{ $query }}"
                                    @endisset
                                placeholder="{{ translate('I am shopping for...') }}" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="la la-search la-flip-horizontal fs-18"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="typed-search-box-mobile stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100"
                    style="min-height: 200px">
                    <div class="search-preloader-mobile absolute-top-center">
                        <div class="dot-loader">
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <div class="search-nothing-mobile d-none p-3 text-center fs-16">

                    </div>
                    <div id="search-content-mobile" class="text-left">

                    </div>
                </div>
            </div>
        </div>
        {{-- @if (Route::currentRouteName() != 'home') --}}
        <div class="hover-category-menu position-absolute w-100 top-140 left-0 right-0 d-none z-3"
            id="hover-category-menu">
            <div class="container">
                <div class="row gutters-10 position-relative">
                    <div class="col-lg-3 position-static">
                        @include('frontend.partials.category_menu', ['class' => 'bg-white shadow-lg'])
                    </div>
                </div>
            </div>
        </div>
        {{-- @endif --}}
        <div class="col-xl-3 side-filter d-xl-none">
            <div class="aiz-categories-sidebar collapse-sidebar-wrap sidebar-xl sidebar-left z-1035">
                <div class="overlay overlay-fixed dark c-pointer" data-toggle="class-toggle"
                    data-target=".aiz-categories-sidebar" data-same=".filter-sidebar-thumb"></div>
                <div class="collapse-sidebar c-scrollbar-light text-left">
                    <div class="d-flex d-xl-none justify-content-between align-items-center pl-3 border-bottom">
                        <h3 class="h6 mb-0 fw-600">{{ translate('All Categories') }}</h3>
                        <button type="button" class="btn btn-sm p-2 filter-sidebar-thumb" data-toggle="class-toggle"
                            data-target=".aiz-categories-sidebar" type="button">
                            <i class="las la-times la-2x"></i>
                        </button>
                    </div>
                    <!-- <div class="container d-xl-none"> -->
                    <div style="display: flex; flex-direction: column; height: 100%;">
                        {{-- <div class="col gutters-10 position-relative">
                                <div class="col-12 position-static"> --}}
                        @include('frontend.partials.category_menu', ['class' => 'border-bottom'])
                        {{-- </div>
                            </div> --}}
                        <div class="d-flex d-xl-none justify-content-between align-items-center pl-3 border-bottom">
                            <h3 class="h6 m-2 fw-600">{{ translate('Menu') }}</h3>
                        </div>
                        <ul
                            class="list-inline mb-0 pl-0 mobile-hor-swipe d-flex flex-wrap justify-content-between flex-column">
                            @php
                                // Current locale
                                $lang = app()->getLocale();
                                // Fetch HeaderLink records + translations
                                $headerLinks = \App\Models\HeaderLink::with('translations')->get();
                            @endphp

                            @foreach ($headerLinks as $headerLink)
                                @php
                                    // For the current language, get the 'title' and 'link' fields
                                    $title = $headerLink->getTranslation('slug', $lang) ?? '';
                                    $url = $headerLink->getTranslation('url', $lang) ?? '#';
                                @endphp

                                <li class="list-inline-item mr-0">
                                    <a href="{{ $url }}"
                                        class="opacity-60 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                                        {{ $title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white border-top border-gray-200 py-1 d-none d-xl-block">
        <div class="container d-flex" id="category-menu-container">
            <div class="h-100 d-flex align-items-center" id="category-menu-icon" data-toggle="class-toggle"
                data-target=".aiz-categories-sidebar">
                <div class="dropdown-toggle navbar-light bg-light h-40px w-100% pl-2 rounded c-pointer">
                    <span class="fw-600 fs-16 mr-3">{{ translate('All Categories') }}</span>
                </div>
            </div>

            <!-- Show in desktop only -->
            <ul class="list-inline mb-0 pl-0 mobile-hor-swipe d-none d-xl-flex">
                @php
                    // Current locale
                    $lang = app()->getLocale();
                    // Fetch HeaderLink records + translations
                    $headerLinks = \App\Models\HeaderLink::with('translations')->get();
                @endphp

                @foreach ($headerLinks as $headerLink)
                    @php
                        // For the current language, get the 'title' and 'link' fields
                        $title = $headerLink->getTranslation('slug', $lang) ?? '';
                        $url = $headerLink->getTranslation('url', $lang) ?? '#';
                    @endphp
                    <li class="list-inline-item mr-0">
                        <a href="{{ $url }}"
                            class="opacity-60 fs-14 px-3 py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                            {{ $title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

</header>

<div class="modal fade" id="order_details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div id="order-details-modal-body">

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function toggleCategoryMenuIcon() {
        var categoryMenuContainer = document.getElementsByClassName('hover-category-menu');
        console.log(categoryMenuContainer);
        console.log(window.innerWidth);

        if (window.innerWidth <= 1200) { // xl size is 1200px and above
            //remove id from first div in category-menu-container
            categoryMenuContainer[0].setAttribute('id', '');
        } else {
            //add id to first div in category-menu-container
            categoryMenuContainer[0].setAttribute('id', 'hover-category-menu');
        }

        // check on click on lang_{locale} will redirect to the selected language
        var langChange = document.getElementById('lang-chang');
        langChange.addEventListener('click', function(e) {
            var target = e.target;
            // if (target.classList.contains('dropdown-item')) {
            var locale = target.getAttribute('data-flag');
            // var lang = document.getElementById('lang_' + locale);
            console.log(locale);
            window.location = locale;
            // }
        });
    }

    window.addEventListener('resize', toggleCategoryMenuIcon);

    window.onload = toggleCategoryMenuIcon;
</script>
@section('script')
    <script type="text/javascript">
        function show_order_details(order_id) {
            $('#order-details-modal-body').html(null);

            if (!$('#modal-size').hasClass('modal-lg')) {
                $('#modal-size').addClass('modal-lg');
            }

            $.post('{{ route('orders.details') }}', {
                _token: AIZ.data.csrf,
                order_id: order_id
            }, function(data) {
                $('#order-details-modal-body').html(data);
                $('#order_details').modal();
                $('.c-preloader').hide();
                AIZ.plugins.bootstrapSelect('refresh');
            });
        }
    </script>
@endsection
