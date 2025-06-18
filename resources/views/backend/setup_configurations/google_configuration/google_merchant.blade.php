@extends('backend.layouts.app')
<style>
    #map {
        width: 100%;
        height: 250px;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0 h6">{{ translate('Google Merchant Center CSV Feed') }}</h3>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('CSV Feed URL') }}</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ route('google-merchant-feed') }}" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="copyToClipboard('{{ route('google-merchant-feed') }}')">
                                        {{ translate('Copy') }}
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">{{ translate('Use this URL in Google Merchant Center to sync your products') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0 h6">{{ translate('Simple Steps: Add CSV Feed URL to Google Merchant Center') }}</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group mar-no">
                        <li class="list-group-item text-dark">
                            <strong>1.</strong> {{ translate('Copy CSV URL') }} - {{ translate('Copy the CSV feed URL from above') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>2.</strong> {{ translate('Open Google Merchant Center') }} - {{ translate('Go to merchant.google.com') }}
                            <a href="https://merchant.google.com" target="_blank" class="text-primary ml-2">{{ translate('Open') }}</a>
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>3.</strong> {{ translate('Add Products') }} - {{ translate('Click "Products" > "All Products" > "+ Add Products"') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>4.</strong> {{ translate('Choose Upload Method') }} - {{ translate('Select "Add products from a file"') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>5.</strong> {{ translate('Set Target Countries') }} - {{ translate('Choose where you want to sell > select "Shopping ads" and "Free listings"') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>6.</strong> {{ translate('Create Feed') }} - {{ translate('Click "Create feed" > enter feed name') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>7.</strong> {{ translate('Configure Scheduled Fetch') }} - {{ translate('Select "Scheduled fetch" > paste your CSV feed URL > set "Daily" frequency') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>8.</strong> {{ translate('Create Feed') }} - {{ translate('Click "Create feed" to finish setup') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>9.</strong> {{ translate('Verify Products') }} - {{ translate('Wait 24-48 hours for processing > check "Products" tab for approval status') }}
                        </li>
                        <li class="list-group-item text-dark">
                            <strong>10.</strong> {{ translate('Link to Google Ads') }} - {{ translate('Connect your Merchant Center to Google Ads for shopping campaigns') }}
                        </li>
                    </ul>
                    <div class="alert alert-success mt-3">
                        <small>{{ translate('Done! Your products will sync daily with Google Merchant Center and appear in Google Shopping.') }}</small>
                    </div>
                    <div class="alert alert-info mt-2">
                        <small><strong>{{ translate('Note') }}:</strong> {{ translate('Products need Google approval before appearing in search results (usually takes 1-3 business days).') }}</small>
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
