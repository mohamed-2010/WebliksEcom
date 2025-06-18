@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('All Branchs')}}</h1>
        </div>
        @if(auth()->user()->can('add_new_branch'))
            <div class="col text-right">
                <a href="{{ route('branches.create') }}" class="btn btn-circle btn-info">
                    <span>{{translate('Add New Branch')}}</span>
                </a>
            </div>
        @endif
    </div>
</div>
<br>

<div class="card">
    <form class="" id="sort_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Branchs') }}</h5>
            </div>
            
{{--            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#" onclick="bulk_delete()"> {{translate('Delete selection')}}</a>
                </div>
            </div>
            
            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" name="type" id="type" onchange="sort_products()">
                    <option value="">{{ translate('Sort By') }}</option>
                    <option value="rating,desc" @isset($col_name , $query) @if($col_name == 'rating' && $query == 'desc') selected @endif @endisset>{{translate('Rating (High > Low)')}}</option>
                    <option value="rating,asc" @isset($col_name , $query) @if($col_name == 'rating' && $query == 'asc') selected @endif @endisset>{{translate('Rating (Low > High)')}}</option>
                    <option value="num_of_sale,desc"@isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'desc') selected @endif @endisset>{{translate('Num of Sale (High > Low)')}}</option>
                    <option value="num_of_sale,asc"@isset($col_name , $query) @if($col_name == 'num_of_sale' && $query == 'asc') selected @endif @endisset>{{translate('Num of Sale (Low > High)')}}</option>
                    <option value="unit_price,desc"@isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'desc') selected @endif @endisset>{{translate('Base Price (High > Low)')}}</option>
                    <option value="unit_price,asc"@isset($col_name , $query) @if($col_name == 'unit_price' && $query == 'asc') selected @endif @endisset>{{translate('Base Price (Low > High)')}}</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>--}}
        </div>
    
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
{{--                        @if(auth()->user()->can('branch_delete'))
                            <th>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-all">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </th>
                        @else--}}
                            <th data-breakpoints="lg">#</th>
                        {{--@endif--}}
                        <th>{{translate('Name')}}</th>
                        <th data-breakpoints="md">{{translate('Total Cities')}}</th>
                        <th data-breakpoints="sm" class="text-right">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $key => $product)
                    <tr>
                        {{--@if(auth()->user()->can('branch_delete'))
                            <td>
                                <div class="form-group d-inline-block">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$product->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </td>
                        @else--}}
                            <td>{{ ($key+1) + ($branches->currentPage() - 1)*$branches->perPage() }}</td>
                        {{--@endif--}}
                        <td>
                            {{$product->name}}
                        </td>
                        <td>
                            {{$product->cities->count()}}
                        </td>
                        <td class="text-right">
                            <a class="btn btn-soft-success btn-icon btn-circle btn-sm"  href="{{ route('branches.control_branch_cities', $product->id) }}" target="_blank" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                            @can('branch_edit')
                                    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('branches.edit', ['id'=>$product, 'lang'=>env('DEFAULT_LANGUAGE')] )}}" title="{{ translate('Edit') }}">
                                        <i class="las la-edit"></i>
                                    </a>
                            @endcan
                            @can('branch_delete')
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('branches.destroy', $product->id)}}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $branches->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection


@section('script')

@endsection
