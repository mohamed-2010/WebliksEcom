@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6">{{ translate('Facebook Commerce CSV Feed') }}</h3>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('CSV Feed URL') }}</label>
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ route('get_products_for_facebook_feed') }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" onclick="copyToClipboard('{{ route('get_products_for_facebook_feed') }}')">
                                    {{ translate('Copy') }}
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">{{ translate('Use this URL in Facebook Commerce Manager to sync your products') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0 h6">{{ translate('Quick Steps: Add CSV Feed URL to Facebook Commerce') }}</h3>
            </div>
            <div class="card-body">
                <ul class="list-group mar-no">
                    <li class="list-group-item text-dark">
                        <strong>1.</strong> {{ translate('Copy CSV URL') }} - {{ translate('Copy the CSV feed URL from above') }}
                    </li>
                    <li class="list-group-item text-dark">
                        <strong>2.</strong> {{ translate('Open Commerce Manager') }} - {{ translate('Go to Facebook Business Manager > Commerce Manager') }}
                        <a href="https://www.facebook.com/commerce_manager/" target="_blank" class="text-primary ml-2">{{ translate('Open') }}</a>
                    </li>
                    <li class="list-group-item text-dark">
                        <strong>3.</strong> {{ translate('Access Catalog') }} - {{ translate('Click "Catalogs" > select existing or create new E-commerce catalog') }}
                    </li>
                    <li class="list-group-item text-dark">
                        <strong>4.</strong> {{ translate('Add Feed') }} - {{ translate('Go to "Data Sources" tab > "Add Items" > "Data feed" > "Set up"') }}
                    </li>
                    <li class="list-group-item text-dark">
                        <strong>5.</strong> {{ translate('Enter URL') }} - {{ translate('Select "Use a URL" > paste your CSV feed URL') }}
                    </li>
                    <li class="list-group-item text-dark">
                        <strong>6.</strong> {{ translate('Configure') }} - {{ translate('Name the feed > set update frequency (daily) > click "Next"') }}
                    </li>
                    <li class="list-group-item text-dark">
                        <strong>7.</strong> {{ translate('Upload') }} - {{ translate('Review field mapping > click "Upload"') }}
                    </li>
                    <li class="list-group-item text-dark">
                        <strong>8.</strong> {{ translate('Connect Shop') }} - {{ translate('Go to "Shops" > connect catalog to Facebook/Instagram shop') }}
                    </li>
                </ul>
                <div class="alert alert-success mt-3">
                    <small>{{ translate('Done! Your products will now sync automatically with Facebook Commerce.') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        AIZ.plugins.notify('success', '{{ translate("URL copied to clipboard!") }}');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
@endsection
