@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6 ">{{translate('Mashkor Shippment')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('shippment_method.update') }}" method="POST">
                    <input type="hidden" name="shippment_method" value="mashkor">
                    @csrf
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MASHKOR_SECRET_KEY">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Mashkor Secret Key')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="MASHKOR_SECRET_KEY" value="{{  env('MASHKOR_SECRET_KEY') }}" placeholder="{{ translate('Mashkor Secret Key') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MASHKOR_AUTH_KEY">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Mashkor Auth Key')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="MASHKOR_AUTH_KEY" value="{{  env('MASHKOR_AUTH_KEY') }}" placeholder="{{ translate('Mashkor Auth Key') }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MASHKOR_PRICE">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Mashkor Price')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="MASHKOR_PRICE" value="{{  env('MASHKOR_PRICE') }}" placeholder="{{ translate('Mashkor Price') }}" required>
                        </div>
                    </div>
                    {{--<div class="form-group row">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Mashkor Sandbox Mode')}}</label>
                        </div>
                        <!--<div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input value="1" name="mashkor_sandbox" type="checkbox" @if (get_setting('mashkor_sandbox') == 1)
                                    checked
                                @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>-->
                    </div>--}}
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6 ">{{translate('Armada Delivery')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('shippment_method.update') }}" method="POST">
                    <input type="hidden" name="shippment_method" value="armada">
                    @csrf
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="ARMADA_SECRET_KEY">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Armada Secret Key')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="ARMADA_SECRET_KEY" value="{{  env('ARMADA_SECRET_KEY') }}" placeholder="{{ translate('Armada Secret Key') }}" required>
                        </div>
                    </div>
                    {{--<div class="form-group row">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Armada Sandbox Mode')}}</label>
                        </div>
                       <!-- <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input value="1" name="armada_sandbox" type="checkbox" @if (get_setting('armada_sandbox') == 1)
                                    checked
                                @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>-->
                    </div>--}}
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="ARMADA_PRICE">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('ARMADA Price')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="ARMADA_PRICE" value="{{  env('ARMADA_PRICE') }}" placeholder="{{ translate('ARMADA Price') }}" required>
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
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6 ">{{translate('Quick Delivery')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('shippment_method.update') }}" method="POST">
                    <input type="hidden" name="shippment_method" value="quick">
                    @csrf
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="QUICK_SECRET_KEY">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Quick Secret Key')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="QUICK_SECRET_KEY" value="{{  env('QUICK_SECRET_KEY') }}" placeholder="{{ translate('Quick Secret Key') }}" required>
                        </div>
                    </div>
                    {{--<div class="form-group row">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Quick Sandbox Mode')}}</label>
                        </div>
                      <!--  <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input value="1" name="quick_sandbox" type="checkbox" @if (get_setting('quick_sandbox') == 1)
                                    checked
                                @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>-->
                    </div>--}}
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="QUICK_PRICE">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Quick Price')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="QUICK_PRICE" value="{{  env('QUICK_PRICE') }}" placeholder="{{ translate('Quick Price') }}" required>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{--<div class="col-md-6">
        <div class="card">
            <div class="card-header ">
                <h5 class="mb-0 h6">{{translate('Bkash Credential')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_method" value="bkash">
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="BKASH_CHECKOUT_APP_KEY">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('BKASH CHECKOUT APP KEY')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="BKASH_CHECKOUT_APP_KEY" value="{{  env('BKASH_CHECKOUT_APP_KEY') }}" placeholder="{{translate('BKASH CHECKOUT APP KEY')}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="BKASH_CHECKOUT_APP_SECRET">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('BKASH CHECKOUT APP SECRET')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="BKASH_CHECKOUT_APP_SECRET" value="{{  env('BKASH_CHECKOUT_APP_SECRET') }}" placeholder="{{translate('BKASH CHECKOUT APP SECRET')}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="BKASH_CHECKOUT_USER_NAME">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('BKASH CHECKOUT USER NAME')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="BKASH_CHECKOUT_USER_NAME" value="{{  env('BKASH_CHECKOUT_USER_NAME') }}" placeholder="{{translate('BKASH CHECKOUT USER NAME')}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="BKASH_CHECKOUT_PASSWORD">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('BKASH CHECKOUT PASSWORD')}}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="BKASH_CHECKOUT_PASSWORD" value="{{  env('BKASH_CHECKOUT_PASSWORD') }}" placeholder="{{translate('BKASH CHECKOUT PASSWORD')}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label class="col-from-label">{{translate('Bkash Sandbox Mode')}}</label>
                        </div>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input value="1" name="bkash_sandbox" type="checkbox" @if (get_setting('bkash_sandbox') == 1)
                                    checked
                                @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
--}}
</div>

@endsection
