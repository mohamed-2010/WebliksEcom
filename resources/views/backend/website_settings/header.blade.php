
@extends('backend.layouts.app')

@section('content')

@php
$currentLang = $lang;
@endphp

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Website Header') }}</h1>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Header Setting') }}</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{ translate('Header Logo') }} <br> {{ translate('Image Size W 237 px X H 80 px') }}</label>
                        <div class="col-md-8">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="types[]" value="header_logo">
                                <input type="hidden" name="header_logo" class="selected-files" value="{{ get_setting('header_logo') }}">
                            </div>
                            <div class="file-preview"></div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Show Language Switcher?')}}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="hidden" name="types[]" value="show_language_switcher">
                                <input type="checkbox" name="show_language_switcher" @if( get_setting('show_language_switcher') == 'on') checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Show Currency Switcher?')}}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="hidden" name="types[]" value="show_currency_switcher">
                                <input type="checkbox" name="show_currency_switcher" @if( get_setting('show_currency_switcher') == 'on') checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Enable sticky header?')}}</label>
                        <div class="col-md-8">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="hidden" name="types[]" value="header_sticky">
                                <input type="checkbox" name="header_sticky" @if( get_setting('header_sticky') == 'on') checked @endif>
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{ translate('Topbar Banner') }} <br> {{ translate('Image Size W 1500 px X H 30 px') }} <br>
                                <a style="color: #e0342b;" target="_blank" href="{{url('/public/uploads/top_banner_sample.jpg')}}">{{translate('Download sample')}}</a></label>
                            <div class="col-md-8">
                                <div class="input-group" data-toggle="aizuploader" data-type="image">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                    <input type="hidden" name="types[]" value="topbar_banner">
                                    <input type="hidden" name="topbar_banner" class="selected-files" value="{{ get_setting('topbar_banner') }}">
                                </div>
                                <div class="file-preview"></div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Topbar Banner Link')}} </label>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <input type="hidden" name="types[]" value="topbar_banner_link">
                                    <input type="text" class="form-control" placeholder="{{ translate('Link with') }} http:// {{ translate('or') }} https://" name="topbar_banner_link" value="{{ get_setting('topbar_banner_link') }}">
                                </div>
                            </div>
                        </div>
                        <div class="border-top pt-3 form-group row">
                            <label class="col-md-3 col-from-label">{{translate('Help Line Number')}} </label>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <input type="hidden" name="types[]" value="helpline_number">
                                    <input type="text" class="form-control" placeholder="{{ translate('Enter number') }}" name="helpline_number" value="{{ get_setting('helpline_number') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center pb-5 w-150">
                <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
            </div>
        </div>

    </form>
    <div class="col-md-8 mx-auto">
            {{-- 2) Language Tabs (for the table display) --}}
            @php
                    $languages = \App\Models\Language::all();
                @endphp
        <ul class="nav nav-tabs nav-fill border-light mb-3">
            @foreach ($languages as $languageItem)
                <li class="nav-item">
                    <a class="nav-link text-reset py-3 @if($languageItem->code === $currentLang) active @else bg-soft-dark border-light border-left-0 @endif"
                        href="{{ route('website.header', ['lang' => $languageItem->code]) }}">
                        <img src="{{ static_asset('assets/img/flags/'.$languageItem->code.'.png') }}" height="11" class="mr-1">
                        {{ $languageItem->name }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- 3) Manage Header Links (Table + "Add New Link" button) --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ translate('Header Links') }}</h6>
            </div>
            <div class="card-body">
                <div class="text-right mb-3">
                    <button type="button" class="btn btn-primary" onclick="openLinkModal()">
                        {{ translate('Add New Link') }}
                    </button>
                </div>

                {{-- Table of existing links, but showing only the selected language’s fields --}}
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ translate('ID') }}</th>
                            <th>{{ translate('Title') }} ({{ $currentLang }})</th>
                            <th>{{ translate('Link') }} ({{ $currentLang }})</th>
                            <th>{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Fetch all links, but in the table we only show the selected language
                            $links = \App\Models\HeaderLink::orderBy('id','desc')->get();
                        @endphp
                        @foreach ($links as $link)
                            <tr>
                                <td>{{ $link->id }}</td>
                                <td>
                                    {{-- Show the "title" translation for $currentLang (could be null/blank if not translated) --}}
                                    {{ $link->getTranslation('slug', $currentLang) }}
                                </td>
                                <td>
                                    {{-- Show the "link" translation for $currentLang --}}
                                    {{ $link->getTranslation('url', $currentLang) }}
                                </td>
                                <td>
                                    <button class="btn btn-icon btn-circle btn-sm btn-soft-primary"
                                            onclick="openLinkModal({{ $link->id }})">
                                        <i class="las la-edit"></i>
                                    </button>
                                    <button class="btn btn-icon btn-circle btn-sm btn-soft-danger"
                                            onclick="deleteLink({{ $link->id }})">
                                        <i class="las la-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 4) The Modal with language tabs to edit ALL languages at once --}}
<div class="modal fade" id="headerLinkModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg">
   <form id="headerLinkForm">
       @csrf
       <input type="hidden" name="id" id="headerLinkId" value="">
       
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">{{ translate('Header Link') }}</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <span>&times;</span>
               </button>
           </div>
           
           <div class="modal-body">
               @php $allLanguages = \App\Models\Language::all(); @endphp
               <ul class="nav nav-tabs" role="tablist">
                   @foreach ($allLanguages as $index => $lang)
                       <li class="nav-item">
                           <a class="nav-link @if($index==0) active @endif"
                              data-toggle="tab"
                              href="#tab-{{ $lang->code }}"
                              role="tab">
                               <img src="{{ static_asset('assets/img/flags/'.$lang->code.'.png') }}"
                                    height="11" class="mr-1">
                               {{ $lang->name }}
                           </a>
                       </li>
                   @endforeach
               </ul>

               <div class="tab-content pt-3">
                   @foreach ($allLanguages as $index => $lang)
                       <div class="tab-pane fade @if($index==0) show active @endif"
                            id="tab-{{ $lang->code }}"
                            role="tabpanel">
                           {{-- Title (this language) --}}
                           <div class="form-group">
                               <label>{{ translate('Title') }} ({{ $lang->code }})</label>
                               <input type="text"
                                      class="form-control"
                                      name="title[{{ $lang->code }}]"
                                      id="title_{{ $lang->code }}"
                                      placeholder="{{ translate('Enter title') }}">
                           </div>
                           {{-- Link (this language) --}}
                           <div class="form-group">
                               <label>{{ translate('Link') }} ({{ $lang->code }})</label>
                               <input type="text"
                                      class="form-control"
                                      name="link[{{ $lang->code }}]"
                                      id="link_{{ $lang->code }}"
                                      placeholder="{{ translate('http:// or https://') }}">
                           </div>
                       </div>
                   @endforeach
               </div>
           </div> 
           
           <div class="modal-footer">
               <button type="button" class="btn btn-light" data-dismiss="modal">
                   {{ translate('Close') }}
               </button>
               <button type="submit" class="btn btn-primary">
                   {{ translate('Save') }}
               </button>
           </div>
       </div>
   </form>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
// We need access to all languages for resetting fields in openLinkModal:
let allLanguages = @json($allLanguages ?? \App\Models\Language::all());

function openLinkModal(linkId = null) {
   // Reset the form
   document.getElementById('headerLinkForm').reset();
   document.getElementById('headerLinkId').value = '';

   // Clear each language's title/link inputs
   allLanguages.forEach(lang => {
       let code = lang.code;
       document.getElementById('title_' + code).value = '';
       document.getElementById('link_' + code).value = '';
   });

   // If editing, fetch data to populate
   if (linkId) {
       axios.get("{{ url('admin/header-links') }}/" + linkId)
           .then(function(response) {
               let data = response.data.data;
               document.getElementById('headerLinkId').value = data.id;
               
               if (data.translations) {
                   data.translations.forEach(trans => {
                       let code = trans.lang; 
                       // Fill title_XX
                       let titleField = document.getElementById('title_' + code);
                       if (titleField) titleField.value = trans.slug ?? '';
                       // Fill link_XX
                       let linkField = document.getElementById('link_' + code);
                       if (linkField) linkField.value = trans.url ?? '';
                   });
               }

               $('#headerLinkModal').modal('show');
           })
           .catch(function(error) {
               alert("{{ translate('Error fetching link data') }}");
           });
   } else {
       // New link
       $('#headerLinkModal').modal('show');
   }
}

// Handle form submission
document.getElementById('headerLinkForm').addEventListener('submit', function(e) {
   e.preventDefault();
   let formData = new FormData(e.target);

   axios.post("{{ route('header_links.save') }}", formData)
       .then(function (response) {
           if (response.data.success) {
               location.reload();
           } else {
               alert(response.data.message || "{{ translate('Error saving link') }}");
           }
       })
       .catch(function (error) {
           console.log(error);
           alert("{{ translate('Error saving link') }}");
       });
});

// Deleting link
function deleteLink(linkId) {
            if (!confirm("{{ translate('Are you sure you want to delete this link?') }}")) {
                return;
            }
            axios.delete("{{ url('admin/header-links') }}/" + linkId, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(function (resp) {
                    if (resp.data.success) {
                        location.reload();
                    } else {
                        alert("{{ translate('Could not delete link') }}");
                    }
                })
                .catch(function (err) {
                    alert("{{ translate('Error deleting link') }}");
                });
        }
</script>
@endsection