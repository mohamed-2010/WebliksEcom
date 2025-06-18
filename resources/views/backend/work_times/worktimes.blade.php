@extends('backend.layouts.app')

@section('content')
    <style>
        th, td {
            border: 1px solid black;
            border-radius: 10px;
            vertical-align: middle!important;
            border-top: 1px solid!important;
        }
        table.footable, table.footable-details {
            border-spacing: 6px!important;
            border-collapse: separate!important;
        }
    </style>
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class=" align-items-center">
            <h1 class="h3">{{translate('Work Times')}}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.worktimesupdate') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <table class="table aiz-table mb-0">
                            @foreach(['friday', 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday'] as $day)
                                <tr>
                                    <th>{{translate(ucfirst($day))}}</th>
                                    <td colspan="2">
                                        <div id="{{ $day }}-slots">
                                            @if(isset($times->$day) && is_array($times->$day))
                                                @foreach($times->$day as $index => $timeSlot)
                                                    <div class="time-slot mb-2">
                                                        {{translate('Open')}}: <input type="time" name="{{ $day }}[{{ $index }}][open]" value="{{ $timeSlot['open'] }}">
                                                        {{translate('Close')}}: <input type="time" name="{{ $day }}[{{ $index }}][close]" value="{{ $timeSlot['close'] }}">
                                                        <button type="button" class="btn btn-danger btn-sm remove-slot" id="remove-slot-{{ $day }}-{{ $index }}">{{translate('Remove')}}</button>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm add-slot" data-day="{{ $day }}">{{translate('Add Time Slot')}}</button>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Update Now') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{translate('General Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-12 col-from-label">{{translate('Specify the number of orders per day')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="order_limit">
                                <input type="text" name="order_limit" class="form-control" value="{{ get_setting('order_limit') }}" placeholder="0 = Unlamented">
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

    <!-- Modal for selecting start and end time -->
    <div class="modal fade" id="timeSlotModal" tabindex="-1" role="dialog" aria-labelledby="timeSlotModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="timeSlotModalLabel">{{translate('Add Time Slot')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="start-time">{{translate('Start Time')}}</label>
                        <input type="time" id="start-time" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="end-time">{{translate('End Time')}}</label>
                        <input type="time" id="end-time" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
                    <button type="button" class="btn btn-primary" id="save-time-slot">{{translate('Save')}}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            let currentDay = '';

            // Add slot button click
            $(document).on('click', '.add-slot', function () {
                currentDay = $(this).data('day');
                $('#timeSlotModal').modal('show');
            });

            // Save time slot from modal
            $('#save-time-slot').on('click', function () {
                const startTime = $('#start-time').val();
                const endTime = $('#end-time').val();

                if (startTime && endTime) {
                    const container = $('#' + currentDay + '-slots');
                    const index = container.find('.time-slot').length;

                    const timeSlot = `
                        <div class="time-slot mb-2">
                            {{translate('Open')}}: <input type="time" name="${currentDay}[${index}][open]" value="${startTime}">
                            {{translate('Close')}}: <input type="time" name="${currentDay}[${index}][close]" value="${endTime}">
                            <button type="button" class="btn btn-danger btn-sm remove-slot" id="remove-slot-${currentDay}-${index}">{{translate('Remove')}}</button>
                        </div>
                    `;
                    container.append(timeSlot);

                    // Add event listener to the newly added remove button
                    $(document).on('click', `#remove-slot-${currentDay}-${index}`, function () {
                        $(this).closest('.time-slot').remove();
                    });

                    $('#timeSlotModal').modal('hide');
                } else {
                    alert('{{translate('Please select both start and end times')}}');
                }
            });

            // Remove slot button click
            $(document).on('click', '[id^=remove-slot-]', function () {
                $(this).closest('.time-slot').remove();
            });
        });
    </script>
@endsection