@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-7 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Create AddOn')}}</h5>
            </div>
            <form class="form-horizontal" action="{{ route('product_addon.store', $category_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{ translate('Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" id="name" name="name" class="form-control" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="price">{{ translate('Price')}}</label>
                        <div class="col-sm-9">
                            <input type="text" id="price" name="price" class="form-control" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Create')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
