@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-7 mx-auto">
        <div class="card">
            <ul class="nav nav-tabs nav-fill border-light">
                @foreach (\App\Models\Language::all() as $key => $language)
                <li class="nav-item">
                    <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3" href="{{ route('product_addon.edit', [$category_id, $productAddOn->id, 'lang'=> $language->code] ) }}">
                        <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                        <span>{{$language->name}}</span>
                    </a>
                </li>
                @endforeach
            </ul>
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Create AddOn')}}</h5>
            </div>
            <form class="form-horizontal" action="{{ route('product_addon.update', [$category_id, $productAddOn->id]) }}" method="POST" enctype="multipart/form-data">
                <input name="_method" type="hidden" value="POST">
                <input type="hidden" name="lang" value="{{ $lang }}">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ translate('Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" id="name" name="name" class="form-control" autocomplete="off" required value="{{ $productAddOn->getTranslation('name', $lang) }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="price">{{ translate('Price')}}</label>
                        <div class="col-sm-9">
                            <input type="text" id="price" name="price" class="form-control" autocomplete="off" required value="{{ $productAddOn->price }}">
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
