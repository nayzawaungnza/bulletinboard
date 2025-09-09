@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card auth-card mx-auto">
            <div class="card-header auth-header">
                {{ __('Verify Your Email') }}
            </div>
            <div class="card-body p-4 text-center">
                <!-- Keep your existing verify content -->
                <!-- ... -->
            </div>
        </div>
    </div>
</div>
@endsection