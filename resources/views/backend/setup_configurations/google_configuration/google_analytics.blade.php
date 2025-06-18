@extends('backend.layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Facebook Pixel Setting') }}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('facebook_pixel.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ translate('Facebook Pixel') }}</label>
                            </div>
                            <div class="col-md-7">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="facebook_pixel" type="checkbox" @if (get_setting('facebook_pixel') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="FACEBOOK_PIXEL_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ translate('Facebook Pixel ID') }}</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="FACEBOOK_PIXEL_ID" value="{{  env('FACEBOOK_PIXEL_ID') }}" placeholder="{{ translate('Facebook Pixel ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-gray-light">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Please be carefull when you are configuring Facebook pixel.') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group mar-no">
                        <li class="list-group-item text-dark">1. {{ translate('Log in to Facebook and go to your Ads Manager account') }}.</li>
                        <li class="list-group-item text-dark">2. {{ translate('Open the Navigation Bar and select Events Manager') }}.</li>
                        <li class="list-group-item text-dark">3. {{ translate('Copy your Pixel ID from underneath your Site Name and paste the number into Facebook Pixel ID field') }}.</li>
                        <li class="list-group-item text-dark">* {{ translate('Link to explain the process') }}. <button class="btn btn-sm btn-primary">
                                <a target="_blank" class="text-white"
                                   href="https://www.facebook.com/business/help/952192354843755?id=1205376682832142">Click here to go to the link</a></button></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Google Analytics Setting')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('google_analytics.update') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{translate('Google Analytics')}}</label>
                            </div>
                            <div class="col-md-7">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="google_analytics" type="checkbox" @if (get_setting('google_analytics') == 1)
                                        checked
                                    @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="TRACKING_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{translate('Tracking ID')}}</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="TRACKING_ID" value="{{  env('TRACKING_ID') }}" placeholder="{{ translate('Tracking ID') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-gray-light">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('How to get Google Analytics Tracking ID.') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group mar-no">

                        <li class="list-group-item text-dark">* {{ translate('Link to explain the process') }}. <button class="btn btn-sm btn-primary">
                                <a target="_blank" class="text-white" href="https://support.google.com/analytics/answer/9304153?hl=en">Click here to go to the link</a></button></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

        {{-- ───────────────── GA-4 E-commerce Tracking ───────────────── --}}
    <div class="row">
        {{-- settings form --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('[GA4] E-commerce Events Tracking') }}</h5>
                </div>

                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('ga4.update') }}" method="POST">
                        @csrf

                        {{-- ON / OFF switch --}}
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ translate('Enable GA-4') }}</label>
                            </div>
                            <div class="col-md-7">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input value="1" name="ga4" type="checkbox"
                                        @if (get_setting('ga4') == 1) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        {{-- Measurement-ID --}}
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MEASUREMENT_ID">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ translate('Measurement ID') }}</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control"
                                    name="MEASUREMENT_ID"
                                    value="{{ env('MEASUREMENT_ID') }}"
                                    placeholder="G-XXXXXXX" required>
                            </div>
                        </div>

                        {{-- API-secret --}}
                        <div class="form-group row">
                            <input type="hidden" name="types[]" value="MEASUREMENT_PROTOCOL_API_SECRET">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{ translate('API Secret') }}</label>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control"
                                    name="MEASUREMENT_PROTOCOL_API_SECRET"
                                    value="{{ env('MEASUREMENT_PROTOCOL_API_SECRET') }}"
                                    placeholder="{{ translate('API Secret') }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-primary">
                                {{ translate('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- help / instructions --}}
        <div class="col-md-6">
            <div class="card bg-gray-light">
                <div class="card-header">
                    <h5 class="mb-0 h6">
                        {{ translate('How to obtain your GA-4 credentials') }}
                    </h5>
                </div>

                <div class="card-body">
                    <ul class="list-group mar-no">
                        <li class="list-group-item text-dark">
                            1. {{ translate('Open Google Analytics ▸ Admin ▸ Data streams.') }}
                        </li>
                        <li class="list-group-item text-dark">
                            2. {{ translate('Click your *Web* stream and copy the “Measurement ID” (looks like G-XXXXXXX).') }}
                        </li>
                        <li class="list-group-item text-dark">
                            3. {{ translate('Scroll to the “Events” card and click “Measurement Protocol API secrets”.') }}
                        </li>
                        <li class="list-group-item text-dark">
                            4. {{ translate('Create a secret, copy the value once it appears, and paste it above.') }}
                        </li>
                        <li class="list-group-item text-dark">
                            * {{ translate('Google help article') }}.
                            <button class="btn btn-sm btn-primary">
                                <a target="_blank" class="text-white"
                                href="https://developers.google.com/analytics/devguides/collection/protocol/ga4">
                                    {{ translate('Click here to open') }}
                                </a>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    {{-- ───────────────────────────────────────────────────────────── --}}

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Sitemap Generator')}}</h5>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('sitemap_generator') }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label class="col-from-label">{{translate('Sitemap Generator')}}</label>
                            </div>
                            <!-- button to generate -->
                            <a href="{{ route('sitemap_generator') }}" class="btn btn-sm btn-primary" id="generate_sitemap">{{translate('Generate Sitemap')}}</a>
                        </div>
                    </form>
                    <!-- show button to copy url of sitemap -->
                    <div class="form-group row">
                        <div class="col-lg-3">
                            <label class="col-from-label">{{translate('Sitemap URL')}}</label>
                        </div>
                        <div class="col-md-7">
                            <input type="text" class="form-control" id="sitemap_url" value="{{ url('/sitemap.xml') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
