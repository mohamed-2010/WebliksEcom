@extends('backend.layouts.app')

@section('content')

    <div class="row">
    	<div class="col-lg-8 mx-auto">
    		<div class="card">
    			<div class="card-header">
    				<h6 class="fw-600 mb-0">{{ translate('General') }}</h6>
    			</div>
    			<div class="card-body">
    				<form action="{{ route('business_settings.update') }}" method="POST">
    					@csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Frontend Website Name')}}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="website_name">
        	                    <input type="text" name="website_name" class="form-control" placeholder="{{ translate('Website Name') }}" value="{{ get_setting('website_name') }}">
                            </div>
                        </div>
    	                <div class="form-group row">
    	                    <label class="col-md-3 col-from-label">{{translate('Site Motto')}}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="site_motto">
        	                    <input type="text" name="site_motto" class="form-control" placeholder="{{ translate('Best eCommerce Website') }}" value="{{  get_setting('site_motto') }}">
                            </div>
    	                </div>
    					<div class="form-group row">
    						<label class="col-md-3 col-from-label">{{ translate('Site Icon') }}</label>
                            <div class="col-md-8">
        						<div class="input-group " data-toggle="aizuploader" data-type="image">
        							<div class="input-group-prepend">
        								<div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
        							</div>
        							<div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="site_icon">
        							<input type="hidden" name="site_icon" value="{{ get_setting('site_icon') }}" class="selected-files">
        						</div>
        						<div class="file-preview box"></div>
        						<small class="text-muted">{{ translate('Website favicon. 32x32 .png') }}</small>
                            </div>
    					</div>
    	                <div class="form-group row">
    	                    <label class="col-md-3 col-from-label">{{translate('Website Base Color')}}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="base_color">
        	                    <input type="text" name="base_color" class="form-control" placeholder="#377dff" value="{{ get_setting('base_color') }}">
        						<small class="text-muted">{{ translate('Hex Color Code') }}</small>
                            </div>
    	                </div>
    	                <div class="form-group row">
    	                    <label class="col-md-3 col-from-label">{{translate('Website Base Hover Color')}}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="base_hov_color">
        	                    <input type="text" name="base_hov_color" class="form-control" placeholder="#377dff" value="{{  get_setting('base_hov_color') }}">
        						<small class="text-muted">{{ translate('Hex Color Code') }}</small>
                            </div>
    	                </div>
    					<div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
                    </form>
    			</div>
    		</div>
    		<div class="card">
    			<div class="card-header">
    				<h6 class="fw-600 mb-0">{{ translate('Global SEO') }}</h6>
    			</div>
                <ul class="nav nav-tabs nav-fill border-light">
                    @foreach (\App\Models\Language::all() as $key => $language)
                        <li class="nav-item">
                            <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('website.appearance', ['lang'=> $language->code] ) }}">
                                <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                                <span>{{$language->name}}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
    			<div class="card-body">
    				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
    					@csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
    					<div class="form-group row">
    						<label class="col-md-3 col-from-label">{{ translate('Meta Title') }}</label>
                            <div class="col-md-8">
        						<input type="hidden" name="types[]" value="meta_title">
        						<input type="text" class="form-control" placeholder="{{translate('Title')}}" name="meta_title" value="{{ get_setting('meta_title',$lang) }}">
                            </div>
    					</div>
    					<div class="form-group row">
    						<label class="col-md-3 col-from-label">{{ translate('Meta description') }}</label>
                            <div class="col-md-8">
        						<input type="hidden" name="types[]" value="meta_description">
        						<textarea class="resize-off form-control" placeholder="{{translate('Description')}}" name="meta_description">{{  get_setting('meta_description',$lang) }}</textarea>
                            </div>
    					</div>
    					<div class="form-group row">
    						<label class="col-md-3 col-from-label">{{ translate('Keywords') }}</label>
                            <div class="col-md-8">
        						<input type="hidden" name="types[]" value="meta_keywords">
        						<textarea class="resize-off form-control" placeholder="{{translate('Keyword, Keyword')}}" name="meta_keywords">{{ get_setting('meta_keywords',$lang) }}</textarea>
        						<small class="text-muted">{{ translate('Separate with coma') }}</small>
                            </div>
    					</div>
    					<div class="form-group row">
    						<label class="col-md-3 col-from-label">{{ translate('Meta Image') }}</label>
                            <div class="col-md-8">
        						<div class="input-group " data-toggle="aizuploader" data-type="image">
        							<div class="input-group-prepend">
        								<div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
        							</div>
        							<div class="form-control file-amount">{{ translate('Choose File') }}</div>
        							<input type="hidden" name="types[]" value="meta_image">
        							<input type="hidden" name="meta_image" value="{{ get_setting('meta_image',$lang) }}" class="selected-files">
        						</div>
        						<div class="file-preview box"></div>
                            </div>
    					</div>
    					<div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
    				</form>
    			</div>
    		</div>
            <div class="card">
    			<div class="card-header">
    				<h6 class="fw-600 mb-0">{{ translate('Cookies Agreement') }}</h6>
    			</div>
                <ul class="nav nav-tabs nav-fill border-light">
                    @foreach (\App\Models\Language::all() as $key => $language)
                        <li class="nav-item">
                            <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('website.appearance', ['lang'=> $language->code] ) }}">
                                <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                                <span>{{$language->name}}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
    			<div class="card-body">
    				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
    					@csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
    					<div class="form-group row">
    						<label class="col-md-3 col-from-label">{{ translate('Cookies Agreement Text') }}</label>
                            <div class="col-md-8">
        						<input type="hidden" name="types[]" value="cookies_agreement_text">
        						<textarea name="cookies_agreement_text" rows="4" class="aiz-text-editor form-control" data-buttons='[["font", ["bold"]],["insert", ["link"]]]'>{{ get_setting('cookies_agreement_text',$lang) }}</textarea>
                            </div>
    					</div>
                        <div class="form-group row">
    						<label class="col-md-3 col-from-label">{{translate('Show Cookies Agreement?')}}</label>
    						<div class="col-md-8">
    							<label class="aiz-switch aiz-switch-success mb-0">
    								<input type="hidden" name="types[]" value="show_cookies_agreement">
    								<input type="checkbox" name="show_cookies_agreement" @if( get_setting('show_cookies_agreement',$lang) == 'on') checked @endif>
    								<span></span>
    							</label>
    						</div>
    					</div>
    					<div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
    				</form>
    			</div>
    		</div>
            <div class="card">
    			<div class="card-header">
    				<h6 class="fw-600 mb-0">{{ translate('Website Popup') }}</h6>
    			</div>
    			<div class="card-body">
    				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data" id="website_popup_form">
    					@csrf
    					<div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Show website popup?')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="show_website_popup">
                                    <input type="checkbox" name="show_website_popup" @if( get_setting('show_website_popup') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
						<input type="hidden" name="types[]" value="website_popup_content">
						<input type="hidden" name="website_popup_content" value="" />
                        <div class="form-group row">
                            {{--<label class="col-md-3 col-from-label">{{ translate('Popup content') }}</label>--}}
							<label class="col-md-3 col-from-label">{{ translate('Image url') }}</label>
                            <div class="col-md-8">
								<input type="hidden" name="types[]" value="image_url">
                                {{--<textarea name="website_popup_content" rows="4" class="aiz-text-editor form-control" >{{ get_setting('website_popup_content') }}</textarea>--}}
								<input type="text" name="image_url" class="form-control" value="{{ get_setting('image_url') }}">
                            </div>
                        </div>
						<div class="form-group row">
							<label class="col-md-3 col-from-label">{{ translate('Click url') }}</label>
							<div class="col-md-8">
								<input type="hidden" name="types[]" value="click_url">
								<input type="text" name="click_url" class="form-control" value="{{ get_setting('click_url') }}">
							</div>
						</div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Show Subscriber form?')}}</label>
                            <div class="col-md-8">
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input type="hidden" name="types[]" value="show_subscribe_form">
                                    <input type="checkbox" name="show_subscribe_form" @if( get_setting('show_subscribe_form') == 'on') checked @endif>
                                    <span></span>
                                </label>
                            </div>
                        </div>
    					<div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
    				</form>
    			</div>
    		</div>
            <div class="card">
                <div class="card-header">
                    <h6 class="fw-600 mb-0">{{ translate('Custom Script') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Header custom script - before </head>') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="header_script">
                                <textarea name="header_script" rows="4" class="form-control" placeholder="<script>&#10;...&#10;</script>">{{ get_setting('header_script') }}</textarea>
                                <small>{{ translate('Write script with <script> tag') }}</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Footer custom script - before </body>') }}</label>
                            <div class="col-md-8">
                                <input type="hidden" name="types[]" value="footer_script">
                                <textarea name="footer_script" rows="4" class="form-control" placeholder="<script>&#10;...&#10;</script>">{{ get_setting('footer_script') }}</textarea>
                                <small>{{ translate('Write script with <script> tag') }}</small>
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
	<script>
		$(document).ready(function(){
			$('#website_popup_form').on('submit', function(e){
				e.preventDefault();
				// will add data of image_url and click_url to html code <a href="click_url"><img src="image_url"></a> and will add this code to website_popup_content input
				var image_url = $('input[name="image_url"]').val();
				var click_url = $('input[name="click_url"]').val();
				var website_popup_content = '<a style="padding: 0px;" href="'+click_url+'" traget="_blank"><img style="padding: 0px; width:100%;" src="'+image_url+'"></a>';
				$('input[name="website_popup_content"]').val(website_popup_content);
				// submit the form
				$(this).unbind('submit').submit();
			});
		});
	</script>
@endsection