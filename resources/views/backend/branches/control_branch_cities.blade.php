@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{ $branch->name }}</h5>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-body">
                {{--dropDown for states--}}
                <div class="form-group">
                    <label class="col-md-3 col-form-label">{{translate('States')}}</label>
                    <div class="col-md-9">
                        <select class="form-control aiz-selectpicker mb-2 mb-md-0" name="state_id" id="state_id" onchange="get_cities_by_state()">
                            <option value="">{{ translate('Select State') }}</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <form action="{{ route('branches.add_city', $branch->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="col-md-3 col-form-label">{{ translate('Cities') }}</label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="cities[]" id="cities-dropdown" multiple data-live-search="true">
                                <option value="all">{{ translate('Select All') }}</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}"
                                        @if(in_array($city->id, $branch->cities->pluck('id')->toArray())) selected @endif>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@section('script')

<script type="text/javascript">

function get_cities_by_state() {
    var state_id = $('#state_id').val();
    $.post('{{ route('cities.get_cities_by_state') }}', {_token:'{{ csrf_token() }}', state_id:state_id}, function(data) {
        $('#cities-dropdown').html('<option value="all">{{ translate("Select All") }}</option>');
        $.each(data, function(index, city) {
            $('#cities-dropdown').append('<option value="'+city.id+'">'+city.name+'</option>');
        });
        $('#cities-dropdown').selectpicker('refresh');
    });
}

$(document).ready(function () {
    $('#cities-dropdown').on('changed.bs.select', function (event, clickedIndex, isSelected, previousValue) {
        let selectedOptions = $(this).val() || [];
        let allOption = 'all';

        if (selectedOptions.includes(allOption)) {
            $(this).selectpicker('selectAll');
        } else {
            let allSelected = $(this).find('option[value="all"]').prop('selected');
            if (allSelected) {
                $(this).find('option[value="all"]').prop('selected', false);
            }
        }

        if (!selectedOptions.includes(allOption) && clickedIndex === 0 && !isSelected) {
            $(this).selectpicker('deselectAll');
        }

        $(this).selectpicker('refresh');
    });
});
</script>

@endsection
