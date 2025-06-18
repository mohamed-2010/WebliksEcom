@extends('backend.layouts.app')

@section('content')

    <div class="col-lg-6  mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Profile')}}</h5>
            </div>
            <div class="card-body">
                <form class="form-horizontal" action="{{ route('profile.update', Auth::user()->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return validatePassword()">
                    <input name="_method" type="hidden" value="PATCH">
                	@csrf
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Name')}}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="{{translate('Name')}}" name="name" value="{{ Auth::user()->name }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="name">{{translate('Email')}}</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" placeholder="{{translate('Email')}}" name="email" value="{{ Auth::user()->email }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="new_password">{{ translate('New Password') }}</label>
                        <div class="col-md-10">
                            <input type="password" id="new_password" class="form-control" name="new_password" placeholder="{{translate('New Password')}}" onkeyup="checkPasswordStrength()">
                            <small id="min-length" class="text-danger">•{{translate(' At least 8 characters')}}</small><br>
                            <small id="uppercase" class="text-danger">•{{translate(' At least one uppercase letter')}}</small><br>
                            <small id="lowercase" class="text-danger">•{{translate(' At least one lowercase letter')}}</small><br>
                            <small id="number" class="text-danger">•{{translate(' At least one number')}}</small>
                        </div>
                    </div>
                    {{-- <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="new_password">{{translate('New Password')}}</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" placeholder="{{translate('New Password')}}" name="new_password">
                        </div>
                    </div> --}}

                    <div class="form-group row">
                        <label class="col-md-2 col-form-label" for="confirm_password">{{ translate('Confirm Password') }}</label>
                        <div class="col-md-10">
                            <input type="password" id="confirm_password" class="form-control" placeholder="{{translate('Confirm Password')}}" name="confirm_password" onkeyup="validateConfirmPassword()">
                            <small id="match-password" class="text-danger">Passwords must match</small>
                        </div>
                    </div>

                    {{-- <div class="form-group row">
                        <label class="col-sm-3 col-from-label" for="confirm_password">{{translate('Confirm Password')}}</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" placeholder="{{translate('Confirm Password')}}" name="confirm_password">
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
                        <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Avatar')}} <small>(90x90)</small></label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="avatar" class="selected-files" value="{{ Auth::user()->avatar_original }}">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
@section('script')
    <script>
        function checkPasswordStrength() {
            let password = document.getElementById('new_password').value;
            document.getElementById('min-length').classList.toggle('text-success', password.length >= 8);
            document.getElementById('min-length').classList.toggle('text-danger', password.length < 8);
            document.getElementById('uppercase').classList.toggle('text-success', /[A-Z]/.test(password));
            document.getElementById('uppercase').classList.toggle('text-danger', !/[A-Z]/.test(password));
            document.getElementById('lowercase').classList.toggle('text-success', /[a-z]/.test(password));
            document.getElementById('lowercase').classList.toggle('text-danger', !/[a-z]/.test(password));
            document.getElementById('number').classList.toggle('text-success', /[0-9]/.test(password));
            document.getElementById('number').classList.toggle('text-danger', !/[0-9]/.test(password));
        }

        function validateConfirmPassword() {
            let password = document.getElementById('new_password').value;
            let confirmPassword = document.getElementById('confirm_password').value;
            document.getElementById('match-password').classList.toggle('text-success', password === confirmPassword);
            document.getElementById('match-password').classList.toggle('text-danger', password !== confirmPassword);
        }

        function validatePassword() {
            let password = document.getElementById('new_password').value;
            let confirmPassword = document.getElementById('confirm_password').value;
            let valid = password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password) && password === confirmPassword;
            if (!valid) {
                alert('Please make sure your password meets all requirements.');
            }
            return valid;
        }
    </script>
@endsection
