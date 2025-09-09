@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card auth-card">
            <div class="card-header auth-header">
                {{ __('Create New Account') }}
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('Name') }} *</label>
                                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">{{ __('Email') }} *</label>
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password" class="form-label">{{ __('Password') }} *</label>
                                    <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password-confirm" class="form-label">{{ __('Confirm Password') }} *</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="dob" class="form-label">{{ __('Date of Birth') }}</label>
                                    <input id="dob" type="date" class="form-control" name="dob" value="{{ old('dob') }}">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">{{ __('Phone') }}</label>
                                    <input id="phone" type="tel" class="form-control" name="phone" value="{{ old('phone') }}">
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">{{ __('Address') }}</label>
                                    <textarea id="address" class="form-control" name="address">{{ old('address') }}</textarea>
                                </div>
                                
                                <div class="col-12">
                                    <label for="profile" class="form-label">{{ __('Profile Image') }}</label>
                                    <input id="profile" type="file" class="form-control" name="profile_path">
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary auth-btn">
                                        {{ __('Register') }}
                                    </button>
                                </div>
                                
                                <div class="col-12 text-center mt-3">
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        {{ __('Already have an account? Login') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    
            </div>
        </div>
    </div>
</div>
@endsection