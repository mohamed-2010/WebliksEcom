@extends('backend.layouts.app')

@section('content')

<div class="row justify-content-around">
    {{-- <div class="col-12"> --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Minimum Order Amount Settings')}}</h5>
            </div>
            <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
              <div class="card-body">
                   @csrf
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Minimum Order Amount Check')}}</label>
                        </div>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="hidden" name="types[]" value="minimum_order_amount_check">
                                <input value="1" name="minimum_order_amount_check" type="checkbox" @if (get_setting('minimum_order_amount_check') == 1)
                                    checked
                                @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="minimum_order_amount">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Set Minimum Order Amount')}}</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="minimum_order_amount" value="{{ get_setting('minimum_order_amount') }}" placeholder="{{ translate('Minimum Order Amount') }}" required>

                        </div>
                        <div class="col-md-4" style="
                            display: flex;
                            align-items: center;
                            justify-content: flex-start;
                            flex-direction: row;">
                            <h6>{{App\Models\Currency::where('id', get_setting('system_default_currency'))->first()->symbol}}</h6>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
              </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Order Via Whatsapp Settings')}}</h5>
            </div>
            <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
              <div class="card-body">
                   @csrf
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Enabled')}}</label>
                        </div>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="hidden" name="types[]" value="order_with_whatsapp_enabled">
                                <input value="true" name="order_with_whatsapp_enabled" type="checkbox" @if (get_setting('order_with_whatsapp_enabled') == 'true')
                                    checked
                                @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="order_whatsapp_phone">
                        <div class="col-md-4">
                            <label class="control-label">{{translate('Phone Number')}}</label>
                        </div>
                        <div class="col-8">
                            <input type="text" class="form-control" name="order_whatsapp_phone" value="{{ get_setting('order_whatsapp_phone') }}" placeholder="{{ translate('Phone Number') }}" required>

                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
              </div>
            </form>
        </div>
    {{-- </div> --}}
</div>

@endsection
