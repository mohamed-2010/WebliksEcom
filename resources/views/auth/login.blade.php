@extends('backend.layouts.layout')

@section('css')

<link rel="stylesheet" href="{{ asset('public/assets/css/custom-style-admin.css') }}">

@endsection

@section('content')

<div class="h-100 bg-cover bg-center  d-flex align-items-center"
<!--style="background-image: url({{ uploaded_asset(get_setting('admin_login_background')) }})"-->


        <div class="row no-gutters" style="min-height:100vh;">
            <div class="col-xxl-9 col-lg-7">
                <div class="h-100">
                    <img src="{{ uploaded_asset(get_setting('admin_login_background')) }}" alt="" class="img-fit h-100">
                </div>
            </div>
            <div class="col-xxl-3 col-lg-5">
                <div class=" row align-items-center justify-content-center justify-content-lg-start h-100">
                    <div class="card-body col-xxl-12 p-4 p-lg-5 text-center">
                        <div class="mb-5 text-center">
                            @if(get_setting('system_logo_black') != null)
                                <img src="{{ uploaded_asset(get_setting('system_logo_black')) }}" class="mw-100 mb-4" height="40">
                            @else
                                <img src="{{ static_asset('assets/img/logo.png') }}" class="mw-100 mb-4" height="40">
                            @endif
                            <h1 class="h3 text-primary mb-0">{{ translate('Welcome to') }} {{ env('APP_NAME') }}</h1>
                            <p>{{ translate('Login to your account.') }}</p>
                        </div>
                        <form class="pad-hor" method="POST" role="form" action="{{ route('login') }}" id="loginForm">

                            @csrf
                            <div class="form-group">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus placeholder="{{ translate('Email') }}">
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required placeholder="{{ translate('Password') }}">
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        {{-- <strong>{{ $errors->first('password') }}</strong> --}}
                                    </span>
                                @endif
                            </div>

                            @if(get_setting('google_recaptcha') == 1)
                            <div class="form-group">
                                <div class="g-recaptcha" data-sitekey="{{ env('CAPTCHA_KEY') }}"></div>
                                @if ($errors->has('g-recaptcha-response'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                    </span>
                                @endif
                            </div>
                            @endif

                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <div class="text-left">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <span>{{ translate('Remember Me') }}</span>
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                                @if(env('MAIL_USERNAME') != null && env('MAIL_PASSWORD') != null)
                                    <div class="col-sm-6">
                                        <div class="text-right">
                                            <a href="{{ route('password.request') }}" class="text-reset fs-14">{{translate('Forgot password ?')}}</a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                {{ translate('Login') }}
                            </button>
                        </form>
                        @if (env("DEMO_MODE") == "On")
                            <div class="mt-4">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td>admin@example.com</td>
                                            <td>123456</td>
                                            <td><button class="btn btn-info btn-xs" onclick="autoFill()">{{ translate('Copy') }}</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

</div>


@endsection

@section('script')
        @if(get_setting('google_recaptcha') == 1)
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endif


        <script type="text/javascript">

        @if(get_setting('google_recaptcha') == 1)
        $(document).ready(function(){
            $("#loginForm").on("submit", function(evt) {
                var response = grecaptcha.getResponse();

                if(response.length == 0) {
                    // reCAPTCHA not verified - show error message
                    evt.preventDefault();

                    $(".g-recaptcha").css("border", "2px solid red"); // Add red border
                    $("#recaptcha-error").remove(); // Remove existing error if any
                    $(".g-recaptcha").after('<span id="recaptcha-error" class="text-danger d-block mt-2"><strong>Please verify you are human!</strong></span>');

                    return false;
                }

                // If reCAPTCHA is verified, remove error styles
                $(".g-recaptcha").css("border", "none");
                $("#recaptcha-error").remove();
            });
        });
        @endif
        </script>

    <script type="text/javascript">
        function autoFill(){
            $('#email').val('admin@example.com');
            $('#password').val('123456');
        }
    </script>
@endsection
