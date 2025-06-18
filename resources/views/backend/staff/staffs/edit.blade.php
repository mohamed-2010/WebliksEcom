@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Staff Information')}}</h5>
            </div>

            <form action="{{ route('staffs.update', $staff->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
            	@csrf
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" value="{{ $staff->user->name }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="email">{{translate('Email')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Email')}}" id="email" name="email" value="{{ $staff->user->email }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="mobile">{{translate('Phone')}}</label>
                        <div class="col-sm-9">
                            <input type="text" placeholder="{{translate('Phone')}}" id="mobile" name="mobile" value="{{ $staff->user->phone }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="password">{{translate('Password')}}</label>
                        <div class="col-sm-9">
                            <input type="password" placeholder="{{translate('Password')}}" id="password" name="password" class="form-control">
                            <small id="password-rules" class="form-text text-muted">
                                <ul class="password-requirements">
                                    <li id="length" class="text-danger">•{{translate(' At least 8 characters')}}</li><br>
                                    <li id="uppercase" class="text-danger">•{{translate(' At least one uppercase letter')}}</li><br>
                                    <li id="lowercase" class="text-danger">•{{translate(' At least one lowercase letter')}}</li><br>
                                    <li id="number" class="text-danger">•{{translate(' At least one number')}}</li>
                                </ul>
                            </small>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Role')}}</label>
                        <div class="col-sm-9">
                            <select name="role_id" required class="form-control aiz-selectpicker">
                                @foreach($roles as $role)
                                    <option value="{{$role->id}}" @php if($staff->role_id == $role->id) echo "selected"; @endphp >{{$role->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Multi Select for Branches -->
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Branches')}}</label>
                        <div class="col-sm-9">
                            <select name="branches[]" required class="form-control aiz-selectpicker" multiple>
                                @foreach($branches as $branch)
                                    @php
                                        $branchIds = $branch_user->pluck('branche_id')->toArray();
                                        $isSelected = in_array($branch->id, $branchIds);
                                    @endphp
                                    <option value="{{$branch->id}}" @if($isSelected) selected @endif>{{$branch->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    document.getElementById('password').addEventListener('input', function() {
        let password = this.value;

        // Check password conditions
        let lengthCheck = password.length >= 8;
        let uppercaseCheck = /[A-Z]/.test(password);
        let lowercaseCheck = /[a-z]/.test(password);
        let numberCheck = /[0-9]/.test(password);

        // Update UI based on validation
        document.getElementById('length').classList.toggle('text-success', lengthCheck);
        document.getElementById('length').classList.toggle('text-danger', !lengthCheck);
        document.getElementById('length').innerHTML = lengthCheck ? '•{{translate(' At least 8 characters')}}' : '•{{translate(' At least 8 characters')}}';

        document.getElementById('uppercase').classList.toggle('text-success', uppercaseCheck);
        document.getElementById('uppercase').classList.toggle('text-danger', !uppercaseCheck);
        document.getElementById('uppercase').innerHTML = uppercaseCheck ? '•{{translate(' At least one uppercase letter')}}' : '•{{translate(' At least one uppercase letter')}}';

        document.getElementById('lowercase').classList.toggle('text-success', lowercaseCheck);
        document.getElementById('lowercase').classList.toggle('text-danger', !lowercaseCheck);
        document.getElementById('lowercase').innerHTML = lowercaseCheck ? '•{{translate(' At least one lowercase letter')}}' : '•{{translate(' At least one lowercase letter')}}';

        document.getElementById('number').classList.toggle('text-success', numberCheck);
        document.getElementById('number').classList.toggle('text-danger', !numberCheck);
        document.getElementById('number').innerHTML = numberCheck ? '•{{translate(' At least one number')}}' : '•{{translate(' At least one number')}}';
    });
</script>
@endsection
