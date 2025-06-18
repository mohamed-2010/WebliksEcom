@extends('backend.layouts.app')

@section('content')

<h4 class="text-center text-muted">{{translate('POS Configuration')}}</h4>
<div class="row">
    <div class="col-lg-6">
        @if(!$shops->isEmpty())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('POS Configuration')}}</h5>
            </div>
                <div class="card-body text-center">
                    <label class="aiz-switch aiz-switch-success mb-0">
                        <input type="checkbox" onchange="updateSettings(this, 'pos_activation_for_seller')" @if(get_setting('pos_activation_for_seller') == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>
                </div>
            {{-- @else
                <div class="card-body text-center">
                    <p>{{ translate('No sellers found.') }}</p>
                </div> --}}
        </div>
        @endif
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('POS Configuration')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('pos.update_settings') }}" method="POST">
                    @csrf
                    {{-- <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('POS Title')}}</label>
                        <div class="col-md-8">
                            <input type="hidden" name="types[]" value="pos_title">
                            <input type="text" name="pos_title" class="form-control" placeholder="{{ translate('POS Title') }}" value="{{ $pos_title ?? '' }}">
                        </div>
                    </div> --}}
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{ translate('POS Image') }}</label>
                        <div class="col-md-8">
                            <div class="input-group " data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="types[]" value="pos_image">
                                <input type="hidden" name="pos_image" value="{{ get_setting('pos_image') }}" class="selected-files">
                            </div>
                            <div class="file-preview box"></div>
                            <small class="text-muted">{{ translate('POS invoice logo W 237 px X H 80 px') }}</small>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function updateSettings(el, type){
            if($(el).is(':checked')){
                var value = 1;
            }
            else{
                var value = 0;
            }
            $.post('{{ route('business_settings.update.activation') }}', {_token:'{{ csrf_token() }}', type:type, value:value}, function(data){
                if(data == '1'){
                    AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }
    </script>
        <script>
            $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
            if (options.url.startsWith("http://")) {
                options.url = options.url.replace("http://", "https://");
            }
        });

            const originalFetch = window.fetch;
            window.fetch = function (url, options) {
                if (typeof url === "string" && url.startsWith("http://")) {
                    url = url.replace("http://", "https://");
                }
                return originalFetch(url, options);
            };
        </script>
@endsection
